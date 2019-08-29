<?


class SecurityQuestion {

	private $id;
	private $question;
	private $answers;
	
	private $response;
	
	private $valid;
	
	public function __Construct($id,$question,$answers) {
			
		$this->id = $id;
		$this->question = $question;
		$this->answers = $answers;
		
		$this->valid = FALSE;
	}
	
	public function Valid() {
		return $this->valid;
	}
	
	public function GetId() {
		return $this->id;
	}

	public function GetQuestion() {
		return $this->question;
	}

	public function GetAnswers() {
		return $this->answers;
	}
	
	public function SetResponse($response) {
		$this->response = trim(preg_replace("/[^0-9a-zA-Z]/","",$response));
	}
	
	public function GetResponse() {
		return $this->response;
	}
	
	public function Verify() {
	
		if (strlen($this->GetResponse()) < 1) return FALSE;
	
		foreach($this->GetAnswers() as $answer) {
			if (strtoupper($this->GetResponse()) == strtoupper($answer))  {
				return $this->valid = TRUE;
			}
		}
	}
	

}



?>