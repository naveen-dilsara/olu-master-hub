<?php

namespace Olu\Commander\Api;

use Olu\Commander\Models\Site;

class ApiController {
    
    // POST /api/v1/handshake
    public function handshake() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['url']) || !isset($input['public_key'])) {
            $this->jsonResponse(['error' => 'Invalid Payload'], 400);
            return;
        }

        $siteModel = new Site();
        
        // Check if site exists
        // simplified logic: if generic URL found, update key, else create
        // In reality, we'd verify a secret or token, but for Phase 2 initial test, we auto-register.
        
        // Generate a shared secret or just ack
        $response = [
            'status' => 'success',
            'hub_id' => 'olu-master-001',
            'message' => 'Connection Established'
        ];
        
        // Save to DB
        // We need to implement a 'findByUrl' or 'upsert' in Site model
        // For now, just create new entry
        $siteModel->create($input);

        $this->jsonResponse($response);
    }
    
    // Helper
    private function jsonResponse($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit; // API ends here
    }
}
