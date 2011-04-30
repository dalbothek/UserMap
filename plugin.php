
require_once('./global.php');

class UserMap {
	const jsonPath = "data.json";
	
	
	private $users;

	public function __construct($users=Null) {
		if($users) {
			$this->addAll($users);
		}
	}
	
	public function add($user) {
		if(is_array($user)) {
			$this->add(new UserMapUser($user));
		} else if(is_a($user, "UserMapUser")) {
			$this->users[] = $user;
		} else {
			trigger_error("Wrong format (expected UserMapUser or array)");
		}
	}
	
	public function addAll($users) {
		if(is_array($users)) {
			foreach($users as $user) {
				$this->add($user);
			}
		} else {
			trigger_error("Wrong format (expected array)");
		}
	}
	
	public function save() {
		if(!$json = fopen(self::jsonPath, "w+"))
			return false;
		fwrite($json, json_encode($this->users));
		fclose($json);
		return true;
	}
	
	public function json() {
		echo json_encode($this->users);
	}

}

class UserMapUser {
	public $name;
	public $city;
	public $lat;
	public $long;
	public $url;
	public $img;
	public $info;

	public function __construct($data=Null) {
		if(is_array($data)) {
			$this->set($data);
		} else if (is_string($data)) {
			$this->name = $data;
		} else if($data != Null) {
			trigger_error("Wrong format (expected string or array)");
		}
	}
	
	public function set($data) {
		if(!is_array($data)) {
			trigger_error("Wrong format (expected array)");
		} else if(count($data) < 5) {
			trigger_error("Argument number missmatch (expected 5 or more arguments)");
		} else {
			$this->name = $data[0];
			$this->city = $data[1];
			$this->lat = $data[2];
			$this->long = $data[3];
			$this->url = $data[4];
			if(conut($data > 6)) {
				$this->img = $data[5];
				$this->info = $data[6];
			} else if(count($data) == 6) {
				if(is_array($data[5])) {
					$this->info = $data[5];	
				} else {
					$this->img = $data[5];
				}
			}
		}
	}
}

switch($_GET['do']) {
	case 'post':
		$vbulletin->input->clean_array_gpc('g', array(
    		'lat' => TYPE_NUM,
    		'lng' => TYPE_NUM,
    		'city' => TYPE_NOHTML
		));
		$uid = $vbulletin->userinfo['userid'];
		
		$users = $vbulletin->db->query_write("	UPDATE userfield
												SET 	field7 = 1,
														field5 = {$vbulletin->GPC[lat]},
														field6 = {$vbulletin->GPC[lng]},
														field2 = '" . $db->escape_string($vbulletin->GPC['city']) . "'
												WHERE	userid = $uid");
	case 'get':			
		$map = new UserMap();
		
		$users = $vbulletin->db->query_read_slave("SELECT 	u.userid,
															u.username,
															f.field2, 
															f.field5, 
															f.field6
													FROM " . TABLE_PREFIX . "userfield as f
													NATURAL JOIN " . TABLE_PREFIX . "user as u
													WHERE f.field7 = 1");
		
		$host = "http://{$_SERVER['HTTP_HOST']}";
		
		while ($info = $vbulletin->db->fetch_array($users))
		{
			if(is_numeric($info['field5']) && is_numeric($info['field6']) && $info['field5'] != 0 && $info['field6'] != 0) {
				$user = new UserMapUser();
				$user->name = $info['username'];
				$user->city = utf8_encode($info['field2']);
				$user->lat = $info['field5'];
				$user->long = $info['field6'];
				$user->url ="$host/members/" . $info['userid'] . "-" . $info['username'];
				$user->img = "$host/image.php?u=" . $info['userid'] . "&type=thumb";
				$map->add($user);
			}
		}
		
	
		$map->json();
		break;
	default:
		$templater = vB_Template::create('custom_Benutzerkarte');
  		$templater->register_page_templates(); 
 		$templater->register('navbar', render_navbar_template(construct_navbits(array())));
		print_output($templater->render());  
		quit();
}

