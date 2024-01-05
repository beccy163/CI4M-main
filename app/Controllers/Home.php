<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('Bienvenido');
    }
       
    public function prueba ()
    {
        echo 'Bienvenido al API REST ';
    }


    public function login(){

        
return view('login');
    
    }
//LEER DATOS 
    public function getUsers()
    {
        // Conectar a la base de datos
        $db = \Config\Database::connect();

        // Realizar la consulta
        $query = $db->query("SELECT * FROM usuarios");
        $results = $query->getResultArray();

        // Devolver los resultados como JSON
        return $this->response->setJSON($results);
    }
    
//LEER POR ID
    public function getUser($userId)
    {
        $this->db = \Config\Database::connect();
        $query = $this->db->query("SELECT * FROM usuarios WHERE id = ?", [$userId]);
        $result = $query->getResult(); // Cambiamos a getResult() para obtener múltiples filas
        $rowCount = count($result);
        
        if ($rowCount === 0) {
            // Si no se encontró ningún usuario con ese ID
            return $this->response->setJSON(['error' => 'Usuario no encontrado.']);
        } else {
            // Si se encontró el usuario, retornamos los datos
            return $this->response->setJSON([$result]);
        }
    }
   
    public function insertUser()
{    // Obtener datos del nuevo usuario (pueden venir por POST o JSON)
    $data = $this->request->getPost() ?: $this->request->getJSON();
        // Verificar si se recibieron los datos correctamente
    if (!isset($data->usuario) || !isset($data->nombre) || !isset($data->apellido)
        || !isset($data->correo_electronico) || !isset($data->telefono)) {
        return $this->response->setStatusCode(400)->setJSON(['error' => 'Datos incompletos']);} 
         // Obtener valores de los datos recibidos
    $usuario = $data->usuario;$nombre = $data->nombre; $apellido = $data->apellido;
    $correo_electronico = $data->correo_electronico; $telefono = $data->telefono;
    // Obtener la fecha actual
    $fecha_registro = date('Y-m-d H:i:s'); // Formato: Año-Mes-Día Hora:Minuto:Segundo
    // Conectar a la base de datos
    $db = \Config\Database::connect();
    // Insertar datos en la base de datos
    try {
        $userData = [
            'usuario' => $usuario,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'correo_electronico' => $correo_electronico,
            'telefono' => $telefono,
            'fecha_registro' => $fecha_registro, // Agregar la fecha de registro
            // Agrega otros campos según tu estructura de base de datos
        ];  
        $db->table('usuarios')->insert($userData);
        // Devolver una respuesta de éxito
        return $this->response->setJSON(['message' => 'Usuario insertado correctamente']);
    } catch (\Exception $e) {
        // Manejo de errores
        return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al insertar usuario']);   }
}

    public function updateUser($id)
    {        // Obtener datos a actualizar (pueden venir por PUT)
       $data = $this->request->getJSON(); // Obtener los datos JSON enviados
        // Obtener valores de los datos recibidos
        $usuario = $data->usuario ?? null;    $nombre = $data->nombre ?? null;
        $apellido = $data->apellido ?? null; $correo_electronico = $data->correo_electronico ?? null;
        $telefono = $data->telefono ?? null;
            // Verificar los valores obtenidos
        if (empty($usuario) && empty($nombre) && empty($apellido)
            && empty($correo_electronico) && empty($telefono)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Los datos recibidos están vacíos']);
        }
            // Conectar a la base de datos
        $db = \Config\Database::connect();
            // Construir los datos a actualizar
        $userData = [];
        if (!empty($usuario)) {
            $userData['usuario'] = $usuario;
        }
        if (!empty($nombre)) {
            $userData['nombre'] = $nombre;
        }
        if (!empty($apellido)) {
            $userData['apellido'] = $apellido;
        }
        if (!empty($correo_electronico)) {
            $userData['correo_electronico'] = $correo_electronico;
        }
        if (!empty($telefono)) {
            $userData['telefono'] = $telefono;
        }
    
        // Actualizar datos en la base de datos
        try {
            $db->table('usuarios')->where('id', $id)->update($userData);
    
            // Devolver una respuesta de éxito
            return $this->response->setJSON(['message' => 'Usuario actualizado correctamente']);
        } catch (\Exception $e) {
            // Manejo de errores
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al actualizar usuario']);
        }
    }
    

    public function deactivateUser($userId)
    {
        $this->db = \Config\Database::connect();
        $this->db->transStart(); // Begin a transaction
        
        // Eliminar completamente al usuario de la base de datos
        $this->db->table('usuarios')->where('id', $userId)->delete();
        
        $affectedRows = $this->db->affectedRows(); // Obtener el número de filas afectadas
        
        $this->db->transComplete(); // Complete the transaction
        
        if ($this->db->transStatus() === false) {
            // Si ocurre un error durante la transacción, realizar un rollback
            $this->db->transRollback();
            return $this->response->setJSON(['error' => 'Error deleting the user.']);
        } elseif ($affectedRows === 0) {
            // Si no se encontró ningún usuario con ese ID
            return $this->response->setJSON(['error' => 'Usuario no encontrado.']);
        } else {
            return $this->response->setJSON(['message' => 'Usuario eliminado exitosamente.']);
        }
    }
    
 }