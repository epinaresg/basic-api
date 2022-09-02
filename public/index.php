<?php
header("Content-Type: application/json");

define('ROOTPATH', __DIR__);


$json = file_get_contents('php://input');

$action = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];


// CREATE
if ($method === 'POST' && $action === '/app/users') {
  
  $data = json_decode($json);

  if (!$data->userName || !$data->password) {
    http_response_code(422);
    echo json_encode([
      'message' => 'Invalid fields'
    ]);
    exit();
  }

  try {
    $user = [
      'id' => md5(microtime()),
      'userName' => $data->userName,
      'password' => md5($data->password),
      'token' => md5(time())
    ];
    

    $users = getJSONData();
    $users[] = $user;
    saveJSONData($users);
  
    http_response_code(201);
    exit();
  } catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(['message' => 'Internal error']);
    exit();
  }
  
}

// READ
if ($method === 'GET' && strpos($action, '/app/users/') !== false) {
  
  $data = json_decode($json);

  $id = str_replace('/app/users/', '', $action);
  
  if (!$id) {
    http_response_code(404);
    echo json_encode([
      'message' => 'Invalid user'
    ]);
    exit();
  }
  
  $userData = getUser($id);
  if (!$userData['user']) {
    http_response_code(404);
    echo json_encode([
      'message' => 'Invalid user'
    ]);
    exit();
  }

  try {

    $user = $userData['user'];
  
    http_response_code(200);
    echo json_encode([
      'userName' => $user->userName,
    ]);
    exit();
  } catch (\Throwable $th) {
    print_r($th);
    http_response_code(500);
    echo json_encode(['message' => 'Internal error']);
    exit();
  }
  
}

// UPDATE
if ($method === 'PUT' && strpos($action, '/app/users/') !== false) {
  
  $data = json_decode($json);

  $id = str_replace('/app/users/', '', $action);
  
  if (!$id) {
    http_response_code(404);
    echo json_encode([
      'message' => 'Invalid user'
    ]);
    exit();
  }
  
  $userData = getUser($id);
  if (!$userData['user']) {
    http_response_code(404);
    echo json_encode([
      'message' => 'Invalid user'
    ]);
    exit();
  }

  if (!$data->userName || !$data->password) {
    http_response_code(422);
    echo json_encode([
      'message' => 'Invalid fields'
    ]);
    exit();
  }

  try {

    $user = $userData['user'];
 
    $user->password = md5($data->password);
    $user->token = md5(time());

    $users = getJSONData();
    $users[$userData['key']] = $user;

    saveJSONData($users);
  
    http_response_code(201);
    exit();
  } catch (\Throwable $th) {
    print_r($th);
    http_response_code(500);
    echo json_encode(['message' => 'Internal error']);
    exit();
  }
  
}

// DELETE
if ($method === 'DELETE' && strpos($action, '/app/users/') !== false) {
  
  $data = json_decode($json);

  $id = str_replace('/app/users/', '', $action);
  
  if (!$id) {
    http_response_code(404);
    echo json_encode([
      'message' => 'Invalid user'
    ]);
    exit();
  }
  
  $userData = getUser($id);
  if (!$userData['user']) {
    http_response_code(404);
    echo json_encode([
      'message' => 'Invalid user'
    ]);
    exit();
  }

  try {
 
    $users = getJSONData();
    unset($users[$userData['key']]);

    saveJSONData($users);
  
    http_response_code(201);
    exit();
  } catch (\Throwable $th) {
    print_r($th);
    http_response_code(500);
    echo json_encode(['message' => 'Internal error']);
    exit();
  }
  
}

// LOGIN
if ($method === 'POST' && $action === '/app/auth') {
  
  $data = json_decode($json);

  if (!$data->userName || !$data->password) {
    http_response_code(422);
    echo json_encode([
      'message' => 'Invalid fields'
    ]);
    exit();
  }

  try {

    $userData = getUserByUserName($data->userName);
    if (!$userData['user']) {
      http_response_code(404);
      echo json_encode([
        'message' => 'Invalid user'
      ]);
      exit();
    }
    $user = $userData['user'];

    if (md5($data->password) !== $user->password) {
      http_response_code(404);
      echo json_encode([
        'message' => 'Invalid credentials'
      ]);
      exit();
    }
  
    http_response_code(200);
    echo json_encode([
      'userName' => $user->userName,
      'token' => $user->token,
    ]);
    exit();
  } catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(['message' => 'Internal error']);
    exit();
  }
  
}

function getUser($id) {

  $users = getJSONData();

  $user = null;
  $userKey = null;
  foreach ($users as $k => $u) {
    if ($id === $u->id) {
      $userKey = $k;
      $user = $u;
      break;
    }
  }
  return [
    'key' => $userKey,
    'user' => $user
  ];
}



function getUserByUserName($userName) {

  $users = getJSONData();

  $user = null;
  $userKey = null;
  foreach ($users as $k => $u) {
    if ($userName === $u->userName) {
      $userKey = $k;
      $user = $u;
      break;
    }
  }
  return [
    'key' => $userKey,
    'user' => $user
  ];
}

function getJSONData () {
  
  $data = json_decode(file_get_contents(ROOTPATH . '/../' . DIRECTORY_SEPARATOR . 'data' .DIRECTORY_SEPARATOR . 'user.json'));

  if (!$data)
    return [];

  return $data;
}

function saveJSONData($data)
{
    file_put_contents(ROOTPATH . '/../' . DIRECTORY_SEPARATOR . 'data' .DIRECTORY_SEPARATOR . 'user.json', json_encode($data));
}
