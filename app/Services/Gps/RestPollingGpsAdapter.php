<?php

namespace App\Services\Gps;

use App\Models\GpsIntegration;
use App\Services\Gps\Contracts\GpsAdapter;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RestPollingGpsAdapter implements GpsAdapter
{
    public function test(GpsIntegration $integration): array
    {
        $response = $this->request($integration, $integration->devices_endpoint ?: $integration->positions_endpoint);
        return ['ok' => $response->successful(), 'status' => $response->status(), 'message' => $response->successful() ? 'Koneksi berhasil.' : $response->body()];
    }

    public function pullPositions(GpsIntegration $integration): array
    {
        $response = $this->request($integration, $integration->positions_endpoint);
        $response->throw();
        $json = $response->json();
        $recordsPath = data_get($integration->response_paths ?? [], 'positions');
        $records = filled($recordsPath) ? data_get($json, $recordsPath, []) : $json;

        if (!is_array($records)) {
            throw new \RuntimeException('Path respons posisi tidak menghasilkan array.');
        }

        return array_is_list($records) ? $records : [$records];
    }

    private function request(GpsIntegration $integration, ?string $endpoint)
    {
        if (blank($integration->provider?->base_url) || blank($endpoint)) {
            throw new \RuntimeException('Base URL dan endpoint wajib diisi.');
        }

        $request = Http::acceptJson()->timeout(30)->withHeaders($integration->provider->extra_headers ?? []);
        [$request, $params] = $this->authenticate($request, $integration, $integration->request_parameters ?? []);
        $url = rtrim($integration->provider->base_url, '/').'/'.ltrim($endpoint, '/');
        $method = strtolower($integration->http_method ?: 'GET');

        return $method === 'post' ? $request->post($url, $params) : $request->get($url, $params);
    }

    private function authenticate(PendingRequest $request, GpsIntegration $integration, array $params): array
    {
        $secret = $integration->credential_secret ?: $integration->provider?->api_key;
        $name = $integration->credential_key_name ?: 'Authorization';

        return match ($integration->auth_type) {
            'bearer' => [$request->withToken((string) $secret), $params],
            'basic' => [$request->withBasicAuth((string) $name, (string) $secret), $params],
            'header' => [$request->withHeader($name, (string) $secret), $params],
            'query' => [$request, array_merge($params, [$name => $secret])],
            default => [$request, $params],
        };
    }
}
