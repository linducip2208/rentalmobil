<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function __invoke(Request $r, Provider $provider, PaymentGatewayService $service)
    {
        $signature = $r->header($provider->config['signature_header'] ?? 'X-Signature');
        if ($provider->api_key && ($provider->config['require_signature'] ?? true)) {
            $expected = hash_hmac('sha256', $r->getContent(), $provider->api_key);
            abort_unless(is_string($signature) && hash_equals($expected, $signature), 401);
        } $tx = $service->callback($provider, $r->all(), $r->header($provider->config['event_id_header'] ?? 'X-Event-Id'));

        return response()->json(['ok' => true, 'transaction' => $tx->public_id]);
    }
}
