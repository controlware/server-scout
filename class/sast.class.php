<?php

final class SAST {

	private $socketHost = "10.0.0.24";
    private $socketPort = 1987;
	private $socket;

	public function startSocketConnection(){
		Log::write("Iniciando processo.");

		// Abre conexao com o servidor
		if(!$this->connectToServer()){
			$this->reconnectToServer();
		}
		sleep(1);

		// Roda o processo infinitamente
		while(true){
			// Verifica se de fato ele deve estar rodando
			$this->verifyIfKeepRunning();

			// Verifica se tem alguma mensagem enviada pelo servidor
			$this->verifyServerIncomingRequests();

			// Verifica e executa requisicoes pendentes
			$this->executePenddingRequests();

			// Verifica se o servidor esta online
			$this->verifyServerConnection();

			// Verifica se tem alguma requisicao concluida para informar o servidor
			$this->verifyDoneRequests();

            // Aguarda 1 segundo
            sleep(1);
		}

		// Fecha conexao caso saia do looping
        socket_close($this->socket);
	}

	private function connectToServer(){
		// Verifica se deve desconectar antes
		if(is_object($this->socket)){
			socket_close($this->socket);
		}

		// Inicia conexao com o servidor
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!@socket_connect($this->socket, $this->socketHost, $this->socketPort) || !@socket_set_nonblock($this->socket)){
			Log::write("Não foi possível conectar ao servidor socket no host {$this->socketHost} e porta {$this->socketPort}.");
			return false;
		}
		

		// Verifica a confirmacao que o servidor recebeu a resposta
		$connected = null;
		$i = 0;
		while($i++ < 10){
			$message = socket_read_all($this->socket);
			$json = json_decode($message, true);
			// Verifica se o servidor confirmou o recebimento
			if(isset($json["connected"])){
				$connected = $json["connected"];
				break;
			}
			usleep(500000); // 0,5s
		}
		if($connected === false){
			Log::write("Não foi possível conectar com o servidor socket no host {$this->socketHost} e porta {$this->socketPort}: {$json["error"]}");
			return false;
		}elseif($connected === null){
			Log::write("Não foi possível conectar com o servidor socket no host {$this->socketHost} e porta {$this->socketPort}: Sem resposta do servidor.");
			return false;
		}

		// Retorna sucesso
		Log::write("Conectado com sucesso ao servidor socket no host {$this->socketHost} e porta {$this->socketPort}.");
		return true;
	}

	// Verifica se tem alguma requisicao para ser executada
	private function executePenddingRequests(){
		$dirname = __DIR__."/../request/pending";
		$filenames = scandir($dirname);
		foreach($filenames as $filename){
			if(substr($filename, -4) === ".req"){
				Shell::executePHP(__DIR__."/../command/work-on-requests.php");
				break;
			}
		}
	}

	private function reconnectToServer($attempts = 1){
		// Verifica se deve encerrar o processo
		if($attempts >= 50){
			Log::write("Server Scout encerrado por falta de comunicação.");
			die();
		}

		// Verifica quanto tempo deve esperar para reconectar
		$sleepSeconds = 5;
		Log::write("Tentando reconectar em {$sleepSeconds} segundos. (Tentativa: {$attempts})");
		sleep($sleepSeconds);

		// Tenta conectar no servidor
		if(!$this->connectToServer()){
			// Se der errado, tenta mais uma vez, incrementando o contados de tentativas
			$this->reconnectToServer($attempts + 1);
		}

		// Se chegou aqui eh porque conectou
		return true;
	}

	private function sendDataToServer(Array $data){
		// Inclui o ID da confirmacao nos dados enviados
		$confirmationId = uniqid();
		$data["confirmationId"] = $confirmationId;
		
		// Envia os dados para o servidor
		$data = json_encode($data);
		Log::write("Enviando dados para o servidor: {$data}");
		socket_write($this->socket, $data, strlen($data));

		// Verifica a confirmacao que o servidor recebeu a resposta
		$i = 0;
		while($i++ < 10){
			$message = socket_read_all($this->socket);
			$json = json_decode($message, true);
			// Verifica se o servidor confirmou o recebimento
			if(isset($json["confirmationId"]) && $json["confirmationId"] === $confirmationId){
				return true;
			}
			usleep(500000); // 0,5s
		}

		// Se chegou ate aqui eh porque deu errado
		return false;
	}

	private function verifyDoneRequests(){
		$dirname = __DIR__."/../request/done";
		$filenames = scandir($dirname);
		foreach($filenames as $filename){
			if(substr($filename, -4) === ".req"){
				// Captura os dados da requisicao e mantem apenas o 'id' e o 'result'
				$data = file_get_contents("{$dirname}/{$filename}");
				$data = json_decode($data, true);
				$data = ["id" => $data["id"], "result" => ($data["result"] ?? null)];
				// Envia os dados para o servidor
				if($this->sendDataToServer($data)){
					unlink("{$dirname}/{$filename}");
				}
			}
		}
	}

	private function verifyIfKeepRunning(){
		if(Process::currentPID() !== Process::lastRegistredPID()){
			socket_close($this->socket);
			die();
		}
	}

	private function verifyServerConnection(){
		
		//if(socket_write($this->socket, "", 1) === false){
		if(socket_read($this->socket, 1) === ""){
			// Registra o ultimo erro
			$error = socket_last_error($this->socket);
			$error = trim("{$error} - ".socket_strerror($error));
			Log::write("Houve uma falha na conexão com o servidor.\nÚltimo erro capturado: {$error}");


			// Tenta reconectar
			$this->reconnectToServer();
		}
	}

	private function verifyServerIncomingRequests(){
		while(true){
			// Verifica se chegou dados do servidor
			$data = socket_read_all($this->socket);
			if(strlen($data) === 0){
				return false;
			}
			Log::write("Novos dados recebidos do servidor: {$data}");

			// Verifica se sao dados de uma requisicao
			$data = json_decode($data, true);
			if(!$data || !$data["id"] || !$data["task"] || !$data["confirmationId"]){
				Log::write("Requisição deve conter os campos: id, task, confirmationId.");
				return false;
			}
			
			// Verifica se a requisicao ja nao existe
			if(Request::searchRequestFile($data["id"]) === false){
				// Cria o arquivo de requisicao
				$filename = __DIR__."/../request/pending/{$data["id"]}.req";
				file_put_contents($filename, json_encode($data));
			}

			// Avisa o servidor que os dados foram recebidos com sucesso
			$confirmationData = json_encode(["confirmationId" => $data["confirmationId"]]);
			socket_write($this->socket, $confirmationData, strlen($confirmationData));

			// Aguarda 1 segundo para ver se tem mais uma requisicao
			sleep(1);
		}
	}

}