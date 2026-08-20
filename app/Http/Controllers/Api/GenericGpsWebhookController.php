<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GpsIntegration;
use App\Services\Gps\GpsPositionIngestor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenericGpsWebhookController extends Controller
{
    public function __invoke(Request $request, GpsIntegration $integration, GpsPositionIngestor $ingestor): JsonResponse
    {
        abort_unless($integration->is_active && $integration->adapter_format === 'webhook_json', 404);
        $this->verifySignature($request, $integration);

        $payload = $request->json()->all();
        $path = data_get($integration->response_paths ?? [], 'webhook_records');
        $records = filled($path) ? data_get($payload, $path, []) : $payload;
        if (!is_array($records)) return response()->json(['message' => 'Payload tidak sesuai mapping.'], 422);
        if (!array_is_list($records)) $records = [$records];

        $saved = 0;
        foreach ($records as $record) if (is_array($record) && $ingestor->ingest($integration, $record)) $saved++;
        return response()->json(['received' => count($records), 'saved' => $saved]);
    }

    private function verifySignature(Request $request, GpsIntegration $integration): void
    {
        $secret = $integration->webhook_secret;
        $header = $integration->webhook_signature_header;
        abort_if(blank($secret) || blank($header), 503, 'Webhook secret belum dikonfigurasi.');
        $provided = (string) $request->header($header);
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        abort_unless(hash_equals($expected, preg_replace('/^sha256=/i', '', $provided)), 401);
    }
}
