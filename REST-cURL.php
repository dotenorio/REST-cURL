<?php

/**
 * Classe para requisições REST utilizando cURL
 *
 * @author Fernando Migliorini Tenório <dotenorio@gmail.com>
 * @version 1.0.0
 * @link https://github.com/dotenorio/REST-cURL
 */
class RestCurl {

  /**
   * Endereço do servidor
   * @var string
   */
  private $server = '';
  
  /**
   * Porta do servidor
   * @var string
   */
  private $port = '';

  /**
   * Inicializa a instância
   * 
   * @param string $server (opcional) Endereço do servidor
   * @param string $port (opcional) Porta do servidor
   * @return resource curl 
   */
  public function __construct($server = null, $port = null) {
    if (!is_null($server) and !is_null($port)) {
      $this->server = $server;
      $this->port = $port;
    }
  }
  
  /**
   * Inicializa o resource do cURL
   * 
   * @param string $url URL a ser requisitada
   * @return resource curl 
   */
  private function init($url) {

    // Cria uma nova instância do cURL
    $ch = curl_init();

    // Define URL e outras possíveis opções
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Retorna resource curl
    return $ch;
  }
  
  /**
   * Verifica o array de opções padrões dos métodos de requisição
   * 
   * @param array $url URL da requisição
   * @param array $options Opções do método de requisição
   * @return array Opções padronizadas
   */
  private function verifyOptions(&$url, $options) {

    // Verifica se um servidor padrão foi configurado
    // para automatizar as URLs
    if (!empty($this->server)) {
      $url = $this->server . ':' . $this->port . $url;
    }
  
    //  Verifica curl_options
    if (!isset($options['curl_options']) or empty($options['curl_options']))
      $options['curl_options'] = array();

    //  Verifica se é associativo ou não
    if (!isset($options['associative']) or empty($options['associative']))
      $options['associative'] = false;

    //  Verifica se possui array de autenticação
    if (!isset($options['auth']) or empty($options['auth']))
      $options['auth'] = array();

    //  Verifica se possui postfields
    if (!isset($options['postfields']) or empty($options['postfields']))
      $options['postfields'] = array();

    //  Verifica se o DELETE será feito no modo safe
    if (!isset($options['safe']) or empty($options['safe']))
      $options['safe'] = false;
    
    //  Verifica se a resposta será em JSON
    if (!isset($options['json']))
      $options['json'] = false;

    return $options;
  }

  /**
   * Faz uma requisição GET
   * 
   * @param string $url URL a ser requisitada
   * @param array $options Array de opções
   * <code>
   * // Exemplos de uso do array $options
   * $options1 = array(
   *    'curl_options' => array(
   *      CURLOPT_HTTPAUTH  =>  CURLAUTH_BASIC,
   *      CURLOPT_USERPWD   =>  'login:senha'
   *    ),
   *    'associative' => false,
   *  );
   *
   *  $options2 = array(
   *    'associative' => false,
   *    'auth' => array(
   *      'login' => 'login',
   *      'password' => 'senha'
   *    )
   *  );
   *
   *  $options3 = array(
   *    'associative' => true,
   *    'json' => false
   *  );
   * </code>
   * @return object
   * @throws Exception
   */
  public function GET($url, $options = array()) {

    // Verifica opções
    $options = $this->verifyOptions($url, $options);

    // Cria uma nova instância do cURL
    $ch = $this->init($url);

    // Define possíveis opções
    if (( is_array($options['auth']) ) && (!empty($options['auth']['login']) ) && (!empty($options['auth']['password']) )) {
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, $options['auth']['login'] . ':' . $options['auth']['password']);
    }

    curl_setopt_array($ch, $options['curl_options']);

    try {
      // Executa cURL
      $result = curl_exec($ch);
      $error = curl_error($ch);
      if (!$result && $error) {
        throw new Exception("Não foi possível requisitar o servidor via GET");
      } elseif (!$result && !$error) {
        return true;
      }
    } catch (Exception $e) {
      // Retorna erro
      echo $e->getMessage();
    }

    // Decodifica JSON para array
    if($options['json'] == true){
      $result = json_decode($result, $options['associative']);
    }
    
    // Fecha cURL
    curl_close($ch);

    // Retorna objeto
    return $result;
  }

  /**
   * Faz uma requisição POST
   * 
   * @param string $url URL a ser requisitada
   * @param array $options Array de opções
   * <code>
   * // Exemplos de uso do array $options
   * $options1 = array(
   *    'postfields' => array(
   *      'client' => array(
   *          'login' => "login",
   *          'razao' => "email",
   *          'senha' => "pass"
   *      )
   *    )
   * );
   *  
   * $options2 = array(
   *    'curl_options' => array(
   *      CURLOPT_HTTPAUTH  =>  CURLAUTH_BASIC,
   *      CURLOPT_USERPWD   =>  'login:senha'
   *    ),
   *    'associative' => false,
   *  );
   *
   *  $options3 = array(
   *    'associative' => false,
   *    'auth' => array(
   *      'login' => 'login',
   *      'password' => 'senha'
   *    )
   *  );
   *
   *  $options4 = array(
   *    'associative' => true,
   *    'json' => false
   *  );
   * </code>
   * @return object 
   * @throws Exception
   */
  public function POST($url, $options = array()) {

    // Verifica opções
    $options = $this->verifyOptions($url, $options);

    // Cria uma nova instância do cURL
    $ch = $this->init($url);

    // Converte array em JSON
    $options['postfields'] = json_encode($options['postfields']);

    // Define URL e outras possíveis opções
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $options['postfields']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    if (( is_array($options['auth']) ) && (!empty($options['auth']['login']) ) && (!empty($options['auth']['password']) )) {
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, $options['auth']['login'] . ':' . $options['auth']['password']);
    }

    curl_setopt_array($ch, $options['curl_options']);

    try {
      // Executa cURL
      $result = curl_exec($ch);
      $error = curl_error($ch);
      if (!$result && $error) {
        throw new Exception("Não foi possível requisitar o servidor via POST");
      } elseif (!$result && !$error) {
        return true;
      }
    } catch (Exception $e) {
      // Retorna erro
      echo $e->getMessage();
    }

    // Decodifica JSON para array
    if($options['json'] == true){
      $result = json_decode($result, $options['associative']);
    }

    // Fecha cURL
    curl_close($ch);

    // Retorna objeto
    return $result;
  }

  /**
   * Faz uma requisição PUT
   * 
   * @param string $url URL a ser requisitada
   * @param array $options Array de opções
   * <code>
   * // Exemplos de uso do array $options
   * $options1 = array(
   *    'postfields' => array(
   *      'client' => array(
   *          'login' => "loginPUT",
   *          'razao' => "emailPUT",
   *          'senha' => "passPUT"
   *      )
   *    ),
   * );
   *  
   * $options2 = array(
   *    'curl_options' => array(
   *      CURLOPT_HTTPAUTH  =>  CURLAUTH_BASIC,
   *      CURLOPT_USERPWD   =>  'login:senha'
   *    ),
   *    'associative' => false,
   *  );
   *
   *  $options3 = array(
   *    'associative' => false,
   *    'auth' => array(
   *      'login' => 'login',
   *      'password' => 'senha'
   *    )
   *  );
   *
   *  $options4 = array(
   *    'associative' => true,
   *    'json' => false
   *  );
   * </code>
   * @return object 
   * @throws Exception
   */
  public function PUT($url, $options = array()) {

    // Verifica opções
    $options = $this->verifyOptions($url, $options);

    // Cria uma nova instância do cURL
    $ch = $this->init($url);

    // Converte array em JSON
    $options['postfields'] = json_encode($options['postfields']);

    // Define URL e outras possíveis opções
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $options['postfields']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    if (( is_array($options['auth']) ) && (!empty($options['auth']['login']) ) && (!empty($options['auth']['password']) )) {
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, $options['auth']['login'] . ':' . $options['auth']['password']);
    }

    curl_setopt_array($ch, $options['curl_options']);

    try {
      // Executa cURL
      $result = curl_exec($ch);
      $error = curl_error($ch);
      if (!$result && $error) {
        throw new Exception("Não foi possível requisitar o servidor via PUT");
      } elseif (!$result && !$error) {
        return true;
      }
    } catch (Exception $e) {
      // Retorna erro
      echo $e->getMessage();
    }

    // Decodifica JSON para array
    if($options['json'] == true){
      $result = json_decode($result, $options['associative']);
    }

    // Fecha cURL
    curl_close($ch);

    // Retorna objeto
    return $result;
  }

  /**
   * Faz uma requisição DELETE
   * Caso exista a opção 'safe' executa uma requisição GET
   * 
   * @param string $url
   * @param boolean $safe Se vai usar o DELETE em mode seguro ou não (em testes)
   * @param array $options Array de opções
   * <code>
   * // Exemplos de uso do array $options
   *  
   * $options1 = array(
   *    'curl_options' => array(
   *      CURLOPT_HTTPAUTH  =>  CURLAUTH_BASIC,
   *      CURLOPT_USERPWD   =>  'login:senha'
   *    ),
   *    'associative' => false,
   *  );
   *
   *  $options2 = array(
   *    'associative' => false,
   *    'auth' => array(
   *      'login' => 'login',
   *      'password' => 'senha'
   *    )
   *  );
   *
   *  $options3 = array(
   *    'associative' => true,
   *    'json' => false
   *  );
   * 
   *  $options4 = array(
   *    'safe' => true
   *  );
   * </code>
   * @return object 
   * @throws Exception
   */
  public function DELETE($url, $options = array()) {

    // Verifica opções
    $options = $this->verifyOptions($url, $options);

    // Cria uma nova instância do cURL
    $ch = $this->init($url);

    // Concatena na url o valor /destroy
    if ($options['safe']) {
      $url .= "/destroy";
    }

    // Opções cURL para modo seguro
    if ($options['safe']) {

      $options['postfields'] = array(
        "_method" => "put"
      );
      $options['postfields'] = json_encode($options['postfields']);

      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $options['postfields']);
    }
    // Opções cURL para modo NÃO seguro
    else {

      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    }

    // Define outras opções
    if (( is_array($options['auth']) ) && (!empty($options['auth']['login']) ) && (!empty($options['auth']['password']) )) {
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_USERPWD, $options['auth']['login'] . ':' . $options['auth']['password']);
    }

    curl_setopt_array($ch, $options['curl_options']);

    try {
      // Executa cURL
      $result = curl_exec($ch);
      $error = curl_error($ch);
      if (!$result && $error) {
        throw new Exception("Não foi possível requisitar o servidor via DELETE");
      } elseif (!$result && !$error) {
        return true;
      }
    } catch (Exception $e) {
      // Retorna erro
      echo $e->getMessage();
    }

    // Decodifica JSON para array
    if($options['json'] == true){
      $result = json_decode($result, $options['associative']);
    }

    // Fecha cURL
    curl_close($ch);

    // Retorna objeto
    return $result;
  }

}

?>
