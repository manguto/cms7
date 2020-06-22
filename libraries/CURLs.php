<?php
namespace manguto\cms7\libraries;

class CURLs
{

    // ####################################################################################################
    static private function callAPI(string $method, string $url, $data, $throwException = true)
    {
        {
            $method = strtoupper($method);
        }
        $curl = curl_init();

        { // default parameters
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        }

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'APIKEY: 111111111111111111111',
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if (! $result) {
            if ($throwException) {
                throw new Exception("Não foi possível estabelecer uma conexão.");
            } else {
                return false;
            }
        }
        curl_close($curl);
        return $result;
    }

    // ####################################################################################################
    /**
     * obtencao do conteudo de uma url via GET
     *
     * @param string $url
     * @return mixed[]
     */
    static function get(string $url)
    {
        $return = self::callAPI('GET', $url, false);
        return $return;

        /*
         * $response = json_decode($return, true);
         * $errors = $response['response']['errors'];
         * $data = $response['response']['data'][0];
         * return [
         * 'errors' => $errors,
         * 'data' => $data
         * ];
         */
    }

    // ####################################################################################################
    /**
     * envio de dados via POST para uma url e obtencao do resultado
     *
     * @param string $url
     * @param array $data_array
     * @return mixed[]
     */
    static function post(string $url, array $data_array)
    {
        $result = self::callAPI('POST', $url, json_encode($data_array));
        return $result;

        /*
         * $response = json_decode($result, true);
         * $errors = $response['response']['errors'];
         * $data = $response['response']['data'][0];
         * return [
         * 'errors' => $errors,
         * 'data' => $data
         * ];
         */
    }

    // ####################################################################################################
    /**
     * envio de dados via PUT para uma url e obtencao do resultado
     *
     * @param string $url
     * @param array $data_array
     * @return mixed[]
     */
    static private function put(string $url, array $data_array)
    {
        $update_plan = self::callAPI('PUT', $url, json_encode($data_array));
        $response = json_decode($update_plan, true);
        $errors = $response['response']['errors'];
        $data = $response['response']['data'][0];
        return [
            'errors' => $errors,
            'data' => $data
        ];
    }

    // ####################################################################################################
    /**
     * envio de dados via PUT para uma url e obtencao do resultado
     *
     * @param string $url
     * @param array $data_array
     * @return mixed[]
     */
    static private function delete(string $url, array $data_array)
    {
        return self::callAPI('DELETE', $url, false);
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}

?>
