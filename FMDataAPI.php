<?php
define('FM_API_DEBUG', true); // Sätt till false för att stänga av debugutskrifter

class FMDataAPI {
    private $host;
    private $database;
    private $username;
    private $password;
    private $token;

    public function __construct($host, $database, $username, $password) {
        $this->host = rtrim($host, '/');
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->authenticate();
    }

    private function authenticate() {
        $url = "{$this->host}/fmi/data/vLatest/databases/{$this->database}/sessions";

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_USERPWD => "{$this->username}:{$this->password}",
            CURLOPT_POST => true,
        ]);

        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);

        if (isset($data['response']['token'])) {
            $this->token = $data['response']['token'];
        } else {
            $message = $data['messages'][0]['message'] ?? 'Okänt fel';
            throw new Exception("Autentisering misslyckades: $message");
        }
    }

    private function request($method, $endpoint, $payload = null) {
        $url = "{$this->host}/fmi/data/vLatest/databases/{$this->database}/$endpoint";

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer {$this->token}"
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        if (FM_API_DEBUG) {
            echo "<pre style='background:#f8f8f8;padding:10px;'>";
            echo "REQUEST: $method $url\n";
            if ($payload !== null) {
                echo "PAYLOAD:\n" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            echo "</pre>";
        }

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new Exception("cURL-fel: $error");
        }

        $result = json_decode($response, true);
        $code = $result['messages'][0]['code'] ?? null;

        if ($code !== '0') {
            $message = $result['messages'][0]['message'] ?? 'Okänt fel';
            throw new Exception("FileMaker API-fel ($code): $message");
        }

        return $result['response'] ?? [];
    }

//
//public function findAll
//
public function findAll($layout, $params = []) {
    // Konvertera vanliga parametrar
    if (isset($params['limit'])) {
        $params['_limit'] = $params['limit'];
        unset($params['limit']);
    }

    if (isset($params['offset'])) {
        $params['_offset'] = $params['offset'];
        unset($params['offset']);
    }

    if (isset($params['sort'])) {
        // Skicka _sort som JSON, URL-kodad!
        $params['_sort'] = rawurlencode(json_encode($params['sort'], JSON_UNESCAPED_UNICODE));
        unset($params['sort']);
    }

    // Bygg querystring manuellt
    $queryParts = [];
    foreach ($params as $key => $value) {
        // Allt utom _sort kodas normalt
        if ($key !== '_sort') {
            $value = rawurlencode($value);
        }
        $queryParts[] = "$key=$value";
    }
    $queryString = implode('&', $queryParts);

    $endpoint = "layouts/$layout/records" . ($queryString ? "?$queryString" : "");

    if (defined('FM_API_DEBUG') && FM_API_DEBUG) {
        $fullUrl = "{$this->host}/fmi/data/vLatest/databases/{$this->database}/$endpoint";
        echo "<pre style='background:#eef;padding:10px;'>";
        echo "DEBUG: findAll()\n";
        echo "Full URL: $fullUrl\n";
        echo "Params: " . print_r($params, true);
        echo "</pre>";
    }

    return $this->request('GET', $endpoint);
}
//
//public function find
//
    public function find($layout, $query, $options) {
        $payload = is_array($query) && array_keys($query) !== range(0, count($query) - 1)
            ? ['query' => [$query]]
            : ['query' => $query];

        $options = is_array($options) ? $options : [];
        $payload = array_merge($payload, $options);

        if (FM_API_DEBUG) {
            echo "<pre style='background:#e6f7ff;padding:10px;'>DEBUG: find()
Layout: $layout
Payload: " . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        }

        return $this->request('POST', "layouts/$layout/_find", $payload);
    }

    public function getRecordById($layout, $recordId) {
        if (FM_API_DEBUG) {
            echo "<pre style='background:#e6f7ff;padding:10px;'>DEBUG: getRecordById()
Layout: $layout
Record ID: $recordId</pre>";
        }

        return $this->request('GET', "layouts/$layout/records/$recordId");
    }

    public function create($layout, $fieldData) {
        if (FM_API_DEBUG) {
            echo "<pre style='background:#e6f7ff;padding:10px;'>DEBUG: create()
Layout: $layout
Field Data: " . json_encode($fieldData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        }

        return $this->request('POST', "layouts/$layout/records", ['fieldData' => $fieldData]);
    }

    public function update($layout, $recordId, $fieldData) {
        if (FM_API_DEBUG) {
            echo "<pre style='background:#e6f7ff;padding:10px;'>DEBUG: update()
Layout: $layout
Record ID: $recordId
Field Data: " . json_encode($fieldData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        }

        return $this->request('PATCH', "layouts/$layout/records/$recordId", ['fieldData' => $fieldData]);
    }

    public function delete($layout, $recordId) {
        if (FM_API_DEBUG) {
            echo "<pre style='background:#e6f7ff;padding:10px;'>DEBUG: delete()
Layout: $layout
Record ID: $recordId</pre>";
        }

        return $this->request('DELETE', "layouts/$layout/records/$recordId");
    }

    public function getToken() {
        return $this->token;
    }

    public function logout() {
        $this->request('DELETE', "sessions/{$this->token}");
        $this->token = null;
    }
}

class FMRecord {
    private $recordId;
    private $fieldData;

    public function __construct($record) {
        $this->recordId = $record['recordId'];
        $this->fieldData = $record['fieldData'];
    }

    public function getField($fieldName) {
        return $this->fieldData[$fieldName] ?? null;
    }

    public function getId() {
        return $this->recordId;
    }

    public function getFields() {
        return $this->fieldData;
    }
}

class FMLayout {
    private $layoutName;

    public function __construct($layoutName) {
        $this->layoutName = $layoutName;
    }

    public function getName() {
        return $this->layoutName;
    }
}
