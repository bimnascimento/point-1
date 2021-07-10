<?php 
if (!class_exists('IndeedDoLogs')){
	class IndeedDoLogs{
		private $process_id;
		private $table;
		private $action_id;
		private $type;
		private $db;
		
		public function __construct(){
			global $wpdb;
			$this->db = $wpdb;
			$this->table = $this->db->base_prefix . 'indeed_logs';
		}
		
		public function IndeedDoLogs(){
			
		}
		
		public function get_logs_for_process($id){
			$data = $this->db->get_results('SELECT * FROM ' . $this->table . ' WHERE process_id='.$id.' ORDER BY create_date ASC,id ASC');
			return $data;
		}
		
		public function get_logs_for_process_for_popup($id){
			$return = array();
			$data = $this->db->get_results('SELECT * FROM ' . $this->table . ' WHERE process_id=' . $id . ' ORDER BY create_date ASC,id ASC');
			foreach ($data as $obj){				
				$log = $obj->stage;

				$return[$log]['create_date'] = $obj->create_date;
				$return[$log]['message'] = $obj->message;
				if (strpos($log, '-')!==FALSE){
					$log_arr = explode('-', $log);
					if ($log_arr[1]>0 && $log_arr[1]<100){
						unset($return[$log]);
					}
				}
			}
			return $return;
		}
		
		public function get_last_log_for_backup($id){
			$data = $this->db->get_results('SELECT * FROM ' . $this->table . ' WHERE action_id=' . $id . ' ORDER BY id DESC LIMIT 1');
			if (!empty($data[0]->create_date)) return $data[0]->create_date;
		}
		
		public function get_process_list($limit=''){
			$arr = array();
			if ($this->db->get_var("SHOW TABLES LIKE '".$this->table."'") == $this->table){
				$data = $this->db->get_results('SELECT DISTINCT process_id FROM ' . $this->table . ' ORDER BY process_id DESC ' . $limit );
				if (!empty($data)){
					foreach ($data as $obj){
						$arr[] = $obj->process_id;
					}
				}				
			}		
			return $arr;
		}
		
		public function delete_logs_by_process($id){
			$this->db->query('DELETE FROM ' . $this->table . ' WHERE process_id=' . $id );
		}
		
		public function set_type($type){
			$this->type = $type;
		}
		
		public function set_action_id($id){
			$this->action_id = $id;
		}
		
		public function insert_log($stage, $message, $status = 0){
			$current_date = date("Y-m-d H:i:s", time() );
			$this->db->query('INSERT INTO ' . $this->table . ' values(null, "'.$this->process_id.'", "' . $this->action_id . '", "'.$stage.'", "' . $message . '", "' . $this->type . '", "' . $current_date . '", "' . $status . '");');   
		}
		
		public function create_log($stage, $message, $status = 0){
			$this->set_process_id();
			$this->insert_log($stage, $message, $status);
		}

		
		public function set_process_id($id=FALSE){
			if ($id){
				$this->process_id = $id;
			} else {
				$data = $this->db->get_results('SELECT process_id FROM ' . $this->table . ' ORDER BY id DESC LIMIT 1 ;');
				if (!empty($data[0]->process_id)){
					$this->process_id = (int)$data[0]->process_id + 1;
				} else {
					$this->process_id = 1;
				}				
			}
		}
		
		public function clean_db($date){
			/*
			 * delete all logs older than $date
			 * @param $date timestamp
			 */
			$date = date("Y-m-d H:i:s", $date );
			$this->db->query('DELETE FROM '.$this->table.' WHERE create_date<"'.$date.'";');
		}
		
	}//end of IndeedDoLogs Class
}//end of if