<?php

namespace App\Rules;

use GuzzleHttp\Client;

class imageURL {
  protected $validTypes = ['image/gif', 'image/png', 'image/jpeg', 'image/bmp', 'image/webp'];

  public function validate($attributes, $value, $parameters, $validator) {
    try {
      $this->verifyURL($value);
      $this->verifyContent($value);
    } catch (Exeption $error) {
      $errors = [
        'url' => 'o campo :attributes não é válido.',
        'fetch' => 'url da imagem não disponível',
        'content' => 'url não se refere a uma imagem'
      ];

      $errors[$error->getCode()]
    }
  }

  public function verifyURL($url) {
    if (filter_var($url, FILTER_VALIDATE_URL)) {
      return true;
    }

    throw new Exception('URL Inválida', 'url');
  }

  public function verifyContent($url) {
    $req = new Client();
    $res = $req->head($url);

    if ($res->hasHeader('Content-Type')) {
      $type = $res->getHeader('Content-Type');

      if (array_key_exists($type, $this->$validTypes)) {
        return true;
      }

      throw new Exeption('Tipo inválido', 'content');
    }

    throw new Exeption('Erro ao requisitar URL.', 'fetch');
  }
}

