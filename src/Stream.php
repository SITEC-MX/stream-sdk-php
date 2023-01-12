<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * SiTEC Stream - Plataforma de video de SiTEC
 *
 * v.1.0.0.0 - 2021-09-13
 */
namespace Mpsoft\Stream;

use \GuzzleHttp\Client;
use \Exception;
use \Throwable;

class Stream
{
    private $token = NULL;
    private $host = "stream.sitec-mx.com";

    private $guzzle = NULL;
    private $openapi = NULL;

    public const NO_IMPLEMENTADO = 0;
    public const ENDPOINT_ERROR_GENERAR = 950;
    public const RESPUESTA_NO_DISPONIBLE = 951;
    public const OK = 1;

    function __construct(string $token)
    {
        $this->token = $token;

        $this->guzzle = new Client();
    }

    private function ObtenerURLEndPoint(string $url, array $variables, ?array $querystrings)
    {
        $endpoint_elementos = array();
        $endpoint_elementos[] = $this->host;

        $url_elementos = explode("/", $url);
        foreach($url_elementos as $url_elemento) // Para cada elemento de la URL
        {
            if($url_elemento[0] == "<") // Si es una variable
            {
                $variable_nombre = substr($url_elemento, 1, -1);

                if( isset($variables[$variable_nombre]) ) // Si se proporciona la variable requerida
                {
                    $endpoint_elementos[] = $variables[$variable_nombre];
                }
                else // Si no se proporciona la variable requerida
                {
                    throw new Exception("No se proporcionó la variable '{$variable_nombre}'.");
                }
            }
            else // Si no es una variable
            {
                $endpoint_elementos[] = $url_elemento;
            }
        }

        $querystring = NULL;
        if($querystrings) // Si se proporciona query-string
        {
            $querystring = "?" . http_build_query($querystrings);
        }

        $endpoint_url = implode("/", $endpoint_elementos);
        return "https://{$endpoint_url}{$querystring}";
    }


    private function API_CALL(string $metodo, string $url, ?array $variables=NULL, ?array $querystrings=NULL, ?array $body=NULL)
    {
        $estado = array("estado"=>Stream::NO_IMPLEMENTADO, "mensaje"=>"OK");

        if(!$variables) // Si no se proporcionan las variables
        {
            $variables = array();
        }

        // Inyectamos la empresa al listado de variables
        $variables["empresa"] = $this->empresa;

        // Calculamos la URL de la llamada
        $endpoint_url = NULL;
        try
        {
            $endpoint_url = $this->ObtenerURLEndPoint($url, $variables, $querystrings);
        }
        catch(Throwable $t) // Error al generar la URL de la llamada
        {
            $estado = array("estado"=>Stream::ENDPOINT_ERROR_GENERAR, "mensaje"=>"Error al generar la URL de la llamada.", "debug"=> utf8_encode($t->getMessage()));
        }

        if($endpoint_url) // Si se obtiene la URL de la llamada
        {
            // Generamos las opciones
            $opciones = array();
            $opciones["auth"] = array("app", $this->token);
            $opciones["json"] = $body;

            $response = NULL;
            try
            {
                $response = $this->guzzle->request($metodo, $endpoint_url, $opciones);
            }
            catch(Throwable $t) // Error al generar la URL del Endpoint
            {
                $response = $t->getResponse();
            }

            if($response) // Si hay respuesta
            {
                $response_text = $response->getBody();
                $estado = json_decode($response_text, TRUE);
            }
            else // Si no hay respuesta
            {
                $estado = array("estado"=>Stream::RESPUESTA_NO_DISPONIBLE, "mensaje"=>"Error al obtener la respuesta de la llamada.");
            }
        }

        return $estado;
    }

    private function ObtenerFirmaDeVariables(?array $variables = NULL)
    {
        if(!$variables)
        {
            $variables = array();
        }

        if(! in_array("empresa", $variables)) // Si no se proporciona empresa como variable
        {
            $variables["empresa"] = NULL;
        }

        $variables_proporcionadas = array_keys($variables);
        asort($variables_proporcionadas);
        $variables_key = implode("-", $variables_proporcionadas);

        return $variables_key;
    }


	public function GET_SesionPerfil(?array $variables=NULL,?array $querystrings=NULL){ $url = "sesion/perfil"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_SesionLogin(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "sesion/login"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_SesionLogout(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "sesion/logout"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_SesionTfaGenerar(?array $variables=NULL,?array $querystrings=NULL){ $url = "sesion/tfa-generar"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_SesionTfaHabilitar(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "sesion/tfa-habilitar"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function POST_SesionDesbloquear(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "sesion/desbloquear"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_Videos(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "": $url = "videos"; break; case "id": $url = "videos/<id>"; break;  default: $url = "videos/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_VideosImportar(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "videos/importar"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function DELETE_Videos(?array $variables=NULL,?array $querystrings=NULL){ $url = "videos/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function PATCH_Videos(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "videos/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function POST_Videotokenes(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "videotokenes"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function GET_Aplicaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = NULL; $variables_key = $this->ObtenerFirmaDeVariables($variables); switch($variables_key) { case "": $url = "aplicaciones"; break; case "id": $url = "aplicaciones/<id>"; break;  default: $url = "aplicaciones/<id>"; break; } return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function POST_Aplicaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "aplicaciones"; return $this->API_CALL("POST", $url, $variables, $querystrings, $body); }
	public function DELETE_Aplicaciones(?array $variables=NULL,?array $querystrings=NULL){ $url = "aplicaciones/<id>"; return $this->API_CALL("DELETE", $url, $variables, $querystrings, NULL); }
	public function PATCH_Aplicaciones(?array $variables=NULL,?array $querystrings=NULL,?array $body=NULL){ $url = "aplicaciones/<id>"; return $this->API_CALL("PATCH", $url, $variables, $querystrings, $body); }
	public function GET_Video(?array $variables=NULL,?array $querystrings=NULL){ $url = "<video_uuid>"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }
	public function GET_Resolucion(?array $variables=NULL,?array $querystrings=NULL){ $url = "<video_uuid>/<resolucion_nombre>"; return $this->API_CALL("GET", $url, $variables, $querystrings, NULL); }

}
