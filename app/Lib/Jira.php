<?php

/**
 * Class Jira
 * helper class to manage connection to jira and to talk to the API
 */
class Jira {

	/**
	 * rest request vars
	 */
	const REQUEST_GET    = "GET";
	const REQUEST_POST   = "POST";
	const REQUEST_PUT    = "PUT";
	const REQUEST_DELETE = "DELETE";

	/**
	 * @var string path to jira
	 */
	protected $path = "";

	/**
	 * @var string jira username
	 */
	protected $username = "";

	/**
	 * @var string jira password
	 */
	protected $password = "";

	/**
	 * ini new object with the jira path passed
	 * @param $path
	 */
	public function __construct($path) {
		$this->path = $path;
	}

	/**
	 * set the username and password for the current request
	 * this could change later to a token authentification
	 * @param $username
	 * @param $password
	 */
	public function auth($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * get base information about a ticket
	 * function still contains some warnings - should be fixed
	 * @param $ticket
	 * @return array
	 */
	public function baseInformationForIssue($ticket) {
		$name = $ticket->fields->summary;
		$reporter = $ticket->fields->reporter->name;
		$assignee = $ticket->fields->assignee->name;
		$projectName = $ticket->fields->project->name;
		$projectKey = $ticket->fields->project->key;
		$created = strtotime($ticket->fields->created);
		$priority = $ticket->fields->priority->name;
		$status = $ticket->fields->status->name;
		$dueDate = strtotime($ticket->fields->duedate);

		$fixVersionsRaw = $ticket->fields->fixVersions;
		$fixVersions = array();
		if( is_array($fixVersionsRaw) ) {
			foreach( $fixVersionsRaw as $fv ) {
				$fixVersions[]= $fv->name;
			}
		}

		// collect ticket information
		return array(
			"name" => $name,
			"fixVersions" => $fixVersions,
			"status" => $status,
			"reporter" => $reporter,
			"created" => $created,
			"daysSinceCreation" => self::getDateDifference($created),
			"priority" => $priority,
			"assignee" => $assignee,
			"key" => $ticket->key,
			"projectKey" => $projectKey,
			"projectName" => $projectName,
			"dueDate" => $dueDate,
			"daysUntilDueDate" => self::getDateDifference($dueDate,false),
		);
	}

	/**
	 * return a list of issues, based on a JQL search string
	 * @param $jql
	 * @return mixed
	 */
	public function getIssuesByJql($jql, $fields = "*all") {

		// encode jql string, but decode slashes (/),
		// Jira can only handle them decoded
		return $this->query(static::REQUEST_GET, "search?fields=".$fields."&maxResults=100&jql=".
			str_replace("%252F", "/",
				rawurlencode($jql)
			)
		);
	}

	/**
	 * get all availabel versions of a project
	 * jira does not provide any limit or order features, yet
	 * @param $projectkey
	 * @return mixed
	 */
	public function getVersionsByProject($projectkey) {
		return $this->query(static::REQUEST_GET, "project/".$projectkey."/versions?");
	}

	/**
	 * @param $ticketkey
	 * @param $newData
	 * @return mixed
	 */
	public function updateTicket($ticketkey, $newData, $transition = false) {
		$method = static::REQUEST_PUT;
		$transitionUrl = "";
		if($transition==true){
			$transitionUrl = '/transitions?expand=transitions.fields';
			$method = static::REQUEST_POST;
		}
		return $this->query($method, "issue/".$ticketkey.$transitionUrl, $newData);
	}

	/**
	 * query the API with adding username and password
	 * tries to convert json result to objects
	 * @param $query
	 * @return mixed
	 * @throws Exception
	 */
	protected function query($method, $query, $data = array()) {
		$result = $this->sendRequest( $method, $query, $data );
		if( $result === false ) throw new Exception("It wasn't possible to get jira url ".$this->path.$query." with username ".$this->username);
		return json_decode($result);
	}

	/**
	 * send the actual Jira request by using the passed REST/HTTP method
	 * @param $method
	 * @param $query
	 * @param array $data
	 * @return string
	 * @throws Exception
	 */
	public function sendRequest($method, $query, $data = array()) {

		/**
		 * start to build the header
		 */
		$header = array();
		$header[] = "Content-Type: application/json";

		/**
		 * add authorization
		 */
		if( !empty($this->username) ) {
			$credential = base64_encode($this->username . ':' . $this->password);
			$header[] = "Authorization: Basic " . $credential;
		}

		/**
		 * create the context
		 */
		$context = array(
			"http" => array(
				"method"  => $method,
				"header"  => join("\r\n", $header),
			));
		if ($method=="POST" || $method == "PUT") {
			$__data     = json_encode($data);
			$header[]   = sprintf('Content-Length: %d', strlen($__data));
			$context['http']['header']  = join("\r\n", $header);
			$context['http']['content'] = $__data;
		} else if (!empty($data)) {
			$query .= "?" . http_build_query($data);
		}

		$data = file_get_contents($this->path . $query,
			false,
			stream_context_create($context)
		);
		if (is_null($data)) {
			throw new Exception("JIRA Rest server returns unexpected result.");
		}
		return $data;
	}

	/**
	 * get date difference in days
	 * @param $date
	 * @return int
	 */
	public function getDateDifference($date,$past = true) {
		$difference = floor(((time() - $date) / 60 / 60 / 24)) * $mult = $past!==true? -1:1;
		return $difference;
	}
}
