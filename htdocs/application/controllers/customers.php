<?php 
/*
* Version: MPL 1.1
*
* The contents of this file are subject to the Mozilla Public License
* Version 1.1 (the "License"); you may not use this file except in
* compliance with the License. You may obtain a copy of the License at
* http://www.mozilla.org/MPL/
* 
* Software distributed under the License is distributed on an "AS IS"
* basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
* License for the specific language governing rights and limitations
* under the License.
* 
* The Original Code is "vBilling - VoIP Billing and Routing Platform"
* 
* The Initial Developer of the Original Code is 
* Digital Linx [<] info at digitallinx.com [>]
* Portions created by Initial Developer (Digital Linx) are Copyright (C) 2011
* Initial Developer (Digital Linx). All Rights Reserved.
*
* Contributor(s)
* "Muhammad Naseer Bhatti <nbhatti at gmail.com>"
*
* vBilling - VoIP Billing and Routing Platform
* version 0.1.1
*
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customers extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('groups_model');
		$this->load->model('manage_accounts_model');
        $this->load->model('billing_model');
		//validate login
		if (!user_login())
		{
			redirect ('home/');
		}
		else
		{
			if($this->session->userdata('user_type') == 'customer')
			{
				redirect ('customer/');
			}
		}
	}

	function index()
	{
		$filter_account_num     = '';
		$filter_company         = '';
		$filter_first_name      = '';
		$filter_type            = '';
		$search                 = '';

		$msg_records_found = "Records Found";

		if($this->input->get('searchFilter'))
		{
			$filter_account_num         = $this->input->get('filter_account_num');
			$filter_company             = $this->input->get('filter_company');
			$filter_first_name          = $this->input->get('filter_first_name');
			$filter_type                = $this->input->get('filter_type');
			$search                     = $this->input->get('searchFilter');
			$msg_records_found          = "Records Found Based On Your Search Criteria";
		}

		$data['filter_account_num']     = $filter_account_num;
		$data['filter_company']         = $filter_company;
		$data['filter_first_name']      = $filter_first_name;
		$data['filter_type']            = $filter_type;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'customers/?searchFilter='.$search.'&filter_account_num='.$filter_account_num.'&filter_company='.$filter_company.'&filter_first_name='.$filter_first_name.'&filter_type='.$filter_type.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 2;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		$data['count'] = $this->customer_model->get_all_customers_count($filter_account_num, $filter_company, $filter_first_name, $filter_type);
		$config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);
		$data['msg_records_found'] = "".$data['count']."&nbsp;".$msg_records_found."";

		$data['customers']      =   $this->customer_model->get_all_customers($config['per_page'], $config['uri_segment'], $filter_account_num, $filter_company, $filter_first_name, $filter_type);
		$data['page_name']		=	'view_customers';
		$data['selected']		=	'customers';
		$data['sub_selected']   =   'list_customer';
		$data['page_title']		=	'CUSTOMERS';
		$data['main_menu']	    =	'default/main_menu/main_menu';
		$data['sub_menu']	    =	'default/sub_menu/customer_sub_menu';
		$data['main_content']	=	'customers/customers_view';
		$this->load->view('default/template',$data);
	}

	function new_customer()
	{
		$data['page_name']		=	'new_customers';
		$data['selected']		=	'customers';
		$data['sub_selected']   =   'new_customer';
		$data['page_title']		=	'NEW CUSTOMER';
		$data['main_menu']	    =	'default/main_menu/main_menu';
		$data['sub_menu']	    =	'default/sub_menu/customer_sub_menu';
		$data['main_content']	=	'customers/add_customer_view';
		$this->load->view('default/template',$data);
	}

	function insert_new_customer()
	{
		$data['account_no']     = rand(1,999).rand(1,999);

		$data['firstname']      = $this->input->post('firstname');
		$data['lastname']       = $this->input->post('lastname');
		$data['companyname']    = $this->input->post('companyname');
		$data['email']          = $this->input->post('email');
		$data['account_type']   = $this->input->post('account_type');
		$data['maxcalls']       = $this->input->post('maxcalls');
		$data['address']        = $this->input->post('address');
		$data['city']           = $this->input->post('city');
		$data['state']          = $this->input->post('state');
		$data['zipcode']        = $this->input->post('zipcode');
		$data['country']        = $this->input->post('country');
		$data['prefix']         = $this->input->post('prefix');
		$data['phone']          = $this->input->post('phone');
		$data['timezone']       = $this->input->post('timezone');
		$data['billingcycle']   = $this->input->post('billingcycle');
		$data['creditlimit']    = $this->input->post('creditlimit');
		$data['cdr_check']      = $this->input->post('cdr_check');
		$data['cdr_email']      = $this->input->post('cdr_email');
		$data['access_chk']     = $this->input->post('access_chk');
		$data['username']       = $this->db->escape($this->input->post('username'));
		$data['pass']           = $this->input->post('password');
		$data['password']       = md5($this->input->post('password'));
		$data['email_check']    = $this->input->post('email_check');
		$data['tot_acl_nodes']  = $this->input->post('tot_acl_nodes');
		$data['tot_sip_acc']    = $this->input->post('tot_sip_acc');
        $data['sip_ip']         = $this->input->post('sip_ip');
		$data['group']          = $this->input->post('group');
        $data['billing_cycle'] = $this->input->post('billing_cycle');

		$group_rate_table_name      = $this->groups_model->group_any_cell($data['group'], 'group_rate_table');

		$check_email_in_use = $this->customer_model->check_email_in_use($data['email']);
		$check_group_validity = $this->groups_model->group_valid_invalid($group_rate_table_name);

		$check_username_availability_count = 0;
		if($data['access_chk'] == 'Y')
        {
            $check_username_availability = $this->manage_accounts_model->check_username_availability($data['username']);
            if($check_username_availability->num_rows() > 0) //username already in use
            {
                $check_username_availability_count = 1;
            }
        }


		if ($check_email_in_use->num_rows() > 0 ) //email already in use 
		{
			echo "email_in_use";
			exit;
		}
		else if ($check_group_validity != 'VALID')
		{
			echo "group_invalid";
			exit;
		}
		else if($check_username_availability_count == 1)
		{
			echo "username_in_use";
			exit;
		}
		else
		{
			//insert the new customer
			$insert_id = $this->customer_model->insert_new_customer($data);

			//if userpanel access defined
			if($data['access_chk'] == 'Y')
			{
				//insert customer userpanel credentials 
				$this->customer_model->insert_customer_user_panel_access($data, $insert_id);

				//insert customer access limitations 
				$this->customer_model->insert_customer_access_limitations($data, $insert_id);

				//if checked send userpanel credentials via email 
				if($data['email_check'] == 'Y')
				{
					$this->load->library('email');
					$this->email->from('noreply@digitallinx.com', 'DigitalLinx');
					$this->email->to($data['email']);
					//$this->email->cc('cc@email.com');
					$this->email->subject('Access Credentials');
					$this->email->message('The following are the credentials for accessing Customer Access Panel:<br/><br/>

						<b>Username: &nbsp; '.$this->input->post('username').'</b><br/>
						<b>Password: &nbsp; '.$data['pass'].'</b><br/>
						<br/><br/>

						Thanks & Regards,<br/>
						DigitalLinx,
						');

					$this->email->send();
				}
			}


			//every thing goes fine echo success
			echo "success";
		}

	}

	//edit customer view 
	function edit_customer($customer_id = '')
	{
		$data['customer']       =   $this->customer_model->get_single_customer($customer_id);
		$data['customer_access']       =   $this->customer_model->customer_access($customer_id);
		$data['customer_id']    =   $customer_id;

		$data['page_name']		=	'edit_customer';
		$data['selected']		=	'customers_info';
		$data['sub_selected']   =   '';
		$data['page_title']		=	'UPDATE CUSTOMER';
		$data['main_menu']	    =	'';
		$data['sub_menu']	    =	'';
		$data['main_content']	=	'customers/edit_customer_view';
		$this->load->view('default/template',$data);
	}

	//update customer db
	function update_customer_db()
	{
		$data['customer_id']      = $this->input->post('customer_id');

		$data['firstname']      = $this->input->post('firstname');
		$data['lastname']       = $this->input->post('lastname');
		$data['companyname']    = $this->input->post('companyname');
		$data['email']          = $this->input->post('email');
		$data['oldemail']          = $this->input->post('oldemail');
		$data['account_type']   = $this->input->post('account_type');
		$data['maxcalls']       = $this->input->post('maxcalls');
		$data['address']        = $this->input->post('address');
		$data['city']           = $this->input->post('city');
		$data['state']          = $this->input->post('state');
		$data['zipcode']        = $this->input->post('zipcode');
		$data['country']        = $this->input->post('country');
		$data['prefix']          = $this->input->post('prefix');
		$data['phone']          = $this->input->post('phone');
		$data['timezone']       = $this->input->post('timezone');
		$data['billingcycle']   = $this->input->post('billingcycle');
		$data['creditlimit']    = $this->input->post('creditlimit');
		$data['cdr_check']      = $this->input->post('cdr_check');
		$data['cdr_email']      = $this->input->post('cdr_email');
		$data['tot_acl_nodes']  = $this->input->post('tot_acl_nodes');
		$data['tot_sip_acc']    = $this->input->post('tot_sip_acc');
        $data['sip_ip']         = $this->input->post('sip_ip');
        $data['billing_cycle'] = $this->input->post('billing_cycle');

		$has_user_access = $this->input->post('has_user_access');
		$check_username_availability_count = 0;

		if($has_user_access > 0) //user already has account 
		{
			$data['chng_username']  = $this->input->post('chng_username');
			$data['username']       = $this->db->escape($this->input->post('username'));
			$data['old_username']   = $this->db->escape($this->input->post('old_username'));

			if($data['chng_username'] == 'Y') //if user want to change username
			{
				if($data['username'] != $data['old_username']) //if entered username not equal to previous username 
				{
					$check_username_availability = $this->manage_accounts_model->check_username_availability($data['username']);
					if($check_username_availability->num_rows() > 0) //username already in use
					{
						$check_username_availability_count = 1;
					}
				}
			}
			$data['chng_password']  = $this->input->post('chng_password');
			$data['pass']           = $this->input->post('password');
			$data['password']       = md5($this->input->post('password'));
			$data['email_check']    = $this->input->post('email_check');
		}
		else //this is the new access account 
		{
			$data['access_chk']     = $this->input->post('access_chk');
			$data['username']       = $this->db->escape($this->input->post('username'));

			if($data['access_chk'] == 'Y') //if user want to allow access to the user panel 
			{
				$check_username_availability = $this->manage_accounts_model->check_username_availability($data['username']);
				if($check_username_availability->num_rows() > 0) //username already in use
				{
					$check_username_availability_count = 1;
				}
			}
			$data['pass']           = $this->input->post('password');
			$data['password']       = md5($this->input->post('password'));
			$data['email_check']    = $this->input->post('email_check');
		}




		$data['group']          = $this->input->post('group');

		$group_rate_table_name      = $this->groups_model->group_any_cell($data['group'], 'group_rate_table');
		$check_group_validity = $this->groups_model->group_valid_invalid($group_rate_table_name);

		if($data['email'] != $data['oldemail'])
		{
			$check_email_in_use = $this->customer_model->check_email_in_use($data['email']);

			if ($check_email_in_use->num_rows() > 0 ) //email already in use 
			{
				echo "email_in_use";
				exit;
			}
			else
			{
				if($check_group_validity != 'VALID')
				{
					echo "group_invalid";
					exit;
				}
				else if ($check_username_availability_count == 1)
				{
					echo "username_in_use";
					exit;
				}
				else
				{
					$update = $this->customer_model->update_customer_db($data);

					if($has_user_access > 0) //if user already has access 
					{
						$email_txt = '';
						if($data['chng_username'] == 'Y') //if user want to change username
						{
							if($data['username'] != $data['old_username']) //if entered username not equal to previous username 
							{
								$this->customer_model->update_customer_username($data);
								$email_txt .= '<b>Username:</b> '.$this->input->post('username').'<br/>';
							}
						}

						if($data['chng_password'] == 'Y') //if user want to change password
						{
							$this->customer_model->update_customer_password($data);
							$email_txt .= '<b>Password:</b> '.$data['pass'].'<br/>';
						}

						//update customer access limitations 
						$this->customer_model->update_customer_access_limitations($data, $data['customer_id']);

						if($email_txt != '')
						{
							$this->load->library('email');
							$this->email->from('noreply@digitallinx.com', 'DigitalLinx');
							$this->email->to($data['email']);
							//$this->email->cc('cc@email.com');
							$this->email->subject('Access Credentials');
							$this->email->message('The following are the credentials for accessing Customer Access Panel:<br/><br/>

								'.$email_txt.'
								<br/><br/>

								Thanks & Regards,<br/>
								DigitalLinx,
								');

							$this->email->send();
						}
					}
					else //create new access
					{
						if($data['access_chk'] == 'Y')
						{
							$this->customer_model->insert_customer_user_panel_access($data, $data['customer_id']);

							//insert customer access limitations 
							$this->customer_model->insert_customer_access_limitations($data, $data['customer_id']);

							//if checked send userpanel credentials via email 
							if($data['email_check'] == 'Y')
							{
								$this->load->library('email');
								$this->email->from('noreply@digitallinx.com', 'DigitalLinx');
								$this->email->to($data['email']);
								//$this->email->cc('cc@email.com');
								$this->email->subject('Access Credentials');
								$this->email->message('The following are the credentials for accessing Customer Access Panel:<br/><br/>

									<b>Username: &nbsp; '.$this->input->post('username').'</b><br/>
									<b>Password: &nbsp; '.$data['pass'].'</b><br/>
									<br/><br/>

									Thanks & Regards,<br/>
									DigitalLinx,
									');

								$this->email->send();
							}
						}
					}

					$this->session->set_flashdata('success','Customer updated successfully.');
					echo "success";
				}
			}
		}
		else
		{
			if($check_group_validity != 'VALID')
			{
				echo "group_invalid";
				exit;
			}
			else if ($check_username_availability_count == 1)
			{
				echo "username_in_use";
				exit;
			}
			else
			{
				$update = $this->customer_model->update_customer_db($data);

				if($has_user_access > 0) //if user already has access 
				{
					$email_txt = '';
					if($data['chng_username'] == 'Y') //if user want to change username
					{
						if($data['username'] != $data['old_username']) //if entered username not equal to previous username 
						{
							$this->customer_model->update_customer_username($data);
							$email_txt .= '<b>Username:</b> '.$this->input->post('username').'<br/>';
						}
					}

					if($data['chng_password'] == 'Y') //if user want to change password
					{
						$this->customer_model->update_customer_password($data);
						$email_txt .= '<b>Password:</b> '.$data['pass'].'<br/>';
					}

					//update customer access limitations 
					$this->customer_model->update_customer_access_limitations($data, $data['customer_id']);

					if($email_txt != '')
					{
						$this->load->library('email');
						$this->email->from('noreply@digitallinx.com', 'DigitalLinx');
						$this->email->to($data['email']);
						//$this->email->cc('cc@email.com');
						$this->email->subject('Access Credentials');
						$this->email->message('The following are the credentials for accessing Customer Access Panel:<br/><br/>

							'.$email_txt.'
							<br/><br/>

							Thanks & Regards,<br/>
							DigitalLinx,
							');

						$this->email->send();
					}
				}
				else //create new access
				{
					if($data['access_chk'] == 'Y')
					{
						$this->customer_model->insert_customer_user_panel_access($data, $data['customer_id']);

						//insert customer access limitations 
						$this->customer_model->insert_customer_access_limitations($data, $data['customer_id']);

						//if checked send userpanel credentials via email 
						if($data['email_check'] == 'Y')
						{
							$this->load->library('email');
							$this->email->from('noreply@digitallinx.com', 'DigitalLinx');
							$this->email->to($data['email']);
							//$this->email->cc('cc@email.com');
							$this->email->subject('Access Credentials');
							$this->email->message('The following are the credentials for accessing Customer Access Panel:<br/><br/>

								<b>Username: &nbsp; '.$this->input->post('username').'</b><br/>
								<b>Password: &nbsp; '.$data['pass'].'</b><br/>
								<br/><br/>

								Thanks & Regards,<br/>
								DigitalLinx,
								');

							$this->email->send();
						}
					}
				}
				$this->session->set_flashdata('success','Customer updated successfully.');
				echo "success";
			}
		}
	}

	//enable or disable customer 
	function enable_disable_customer()
	{
		$data['customer_id']       = $this->input->post('customer_id');
		$data['status']             = $this->input->post('status');
		$this->customer_model->enable_disable_customer($data);
	}

	//customer rate view
	function customer_rates($customer_id = '')
	{
		$filter_display_results = 'min';

		//for filter & search
		$filter_start_date   = '';
		$filter_end_date     = '';
		$filter_carriers     = '';
		$filter_rate_type    = '';
		$search              = '';

		$msg_records_found = "Records Found";

		if($this->input->get('searchFilter'))
		{
			$filter_start_date      = $this->input->get('filter_start_date');
			$filter_end_date        = $this->input->get('filter_end_date');
			$filter_carriers        = $this->input->get('filter_carriers');
			$filter_rate_type       = $this->input->get('filter_rate_type');
			$filter_display_results = $this->input->get('filter_display_results');
			$search                 = $this->input->get('searchFilter');
			$msg_records_found      = "Records Found Based On Your Search Criteria";
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}

		if (!checkdateTime($filter_start_date))
		{
			$filter_start_date   = '';
		}

		if (!checkdateTime($filter_end_date))
		{
			$filter_end_date   = '';
		}

		$data['filter_start_date']          = $filter_start_date;
		$data['filter_end_date']            = $filter_end_date;
		$data['filter_carriers']            = $filter_carriers;
		$data['filter_rate_type']           = $filter_rate_type;
		$data['filter_display_results']     = $filter_display_results;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'customers/customer_rates/'.$customer_id.'/?searchFilter='.$search.'&filter_start_date='.$filter_start_date.'&filter_end_date='.$filter_end_date.'&filter_carriers='.$filter_carriers.'&filter_rate_type='.$filter_rate_type.'&filter_display_results='.$filter_display_results.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 6;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}




		$customer_group_id      =   $this->customer_model->customer_any_cell($customer_id, 'customer_rate_group');

		if($customer_group_id != '' && $customer_group_id != '0')
		{
			$customer_group_table   =   $this->groups_model->group_any_cell($customer_group_id, 'group_rate_table');
			$data['count']          =   $this->customer_model->customer_rates_count($customer_group_table, $filter_start_date, $filter_end_date, $filter_carriers, $filter_rate_type);
			$data['rates']          =   $this->customer_model->customer_rates($config['per_page'], $config['uri_segment'], $customer_group_table, $filter_start_date, $filter_end_date, $filter_carriers, $filter_rate_type);
		}
		else
		{
			$data['rates']  = "not_found";
			$data['count']  = 0;
		}


		$config['total_rows'] = $data['count'];
		$this->pagination->initialize($config);

		$data['msg_records_found'] = "".$data['count']."&nbsp;".$msg_records_found."";




		$data['customer_id']    =   $customer_id;
		$data['tbl_name']       =   $customer_group_table;

		$data['page_name']		=	'rates_customer';
		$data['selected']		=	'customers_rate';
		$data['sub_selected']   =   '';
		$data['page_title']		=	'CUSTOMER RATES';
		$data['main_menu']	    =	'';
		$data['sub_menu']	    =	'';
		$data['main_content']	=	'customers/rate_customer_view';
		$this->load->view('default/template',$data);
	}

	function get_country_prefix()
	{
		$id = $this->input->post('id');
		echo country_any_cell($id, 'countryprefix');
	}
	/*
	//enable or disable customer rate 
	function enable_disable_customer_rate()
	{
	$data['rate_id']            = $this->input->post('rate_id');
	$data['status']             = $this->input->post('status');
	$data['tbl_name']           = $this->input->post('tbl_name');

if($data['tbl_name'] != '')
{
$this->customer_model->enable_disable_customer_rate($data);
}
}
*/

//*********************** CUSTOMER ACL NODES FUNCTION ****************************************//

function customer_acl_nodes($customer_id = '')
{
	$data['acl_nodes']      =   $this->customer_model->customer_acl_nodes($customer_id);
	$data['customer_id']    =   $customer_id;

	$data['page_name']		=	'customer_acl_nodes';
	$data['selected']		=	'customers_ip';
	$data['sub_selected']   =   '';
	$data['page_title']		=	'CUSTOMER ACL NODES';
	$data['main_menu']	    =	'';
	$data['sub_menu']	    =	'';
	$data['main_content']	=	'customers/ip_customer_view';
	$this->load->view('default/template',$data);
}

function new_acl_node($customer_id = '')
{
	$data['customer_id']    =   $customer_id;

	$data['page_name']		=	'new_acl_node';
	$data['selected']		=	'new_acl_node';
	$data['sub_selected']   =   '';
	$data['page_title']		=	'NEW ACL NODE';
	$data['main_menu']	    =	'';
	$data['sub_menu']	    =	'';
	$data['main_content']	=	'customers/new_acl_node_view';
	$this->load->view('default/template',$data);
}

function insert_new_acl_node()
{
	$customer_id = $this->input->post('customer_id');
	$ip = $this->input->post('ip');
	$cdr = $this->input->post('cdr');
	$this->customer_model->insert_new_acl_node($customer_id, $ip, $cdr);

	//relaod acl
	$fp = $this->esl->event_socket_create($this->esl->ESL_host, $this->esl->ESL_port, $this->esl->ESL_password);
	$cmd = "api reloadacl";
	$response = $this->esl->event_socket_request($fp, $cmd);
	echo $response; 
	fclose($fp);
}

function edit_acl_node($node_id = '', $customer_id = '')
{
	$data['customer_id']    =   $customer_id;
	$data['acl_node_id']    =   $node_id;
	$data['acl_node']       =   $this->customer_model->customer_acl_nodes_single($node_id, $customer_id);

	if($data['acl_node']->num_rows() == 0)
	{
		redirect('customers/customer_acl_nodes/'.$customer_id.''); 
	}

	$data['page_name']		=	'edit_acl_node';
	$data['selected']		=	'';
	$data['sub_selected']   =   '';
	$data['page_title']		=	'UPDATE ACL NODE';
	$data['main_menu']	    =	'';
	$data['sub_menu']	    =	'';
	$data['main_content']	=	'customers/edit_acl_node_view';
	$this->load->view('default/template',$data);
}

function update_acl_node_db()
{
	$node_id = $this->input->post('node_id');
	$ip = $this->input->post('ip');
	$cdr = $this->input->post('cdr');
	$this->customer_model->update_acl_node_db($node_id, $ip, $cdr);

	//relaod acl
	$fp = $this->esl->event_socket_create($this->esl->ESL_host, $this->esl->ESL_port, $this->esl->ESL_password);
	$cmd = "api reloadacl";
	$response = $this->esl->event_socket_request($fp, $cmd);
	echo $response; 
	fclose($fp);
}

function delete_acl_node()
{
	$node_id = $this->input->post('node_id');
	$this->customer_model->delete_acl_node($node_id);

	//relaod acl
	$fp = $this->esl->event_socket_create($this->esl->ESL_host, $this->esl->ESL_port, $this->esl->ESL_password);
	$cmd = "api reloadacl";
	$response = $this->esl->event_socket_request($fp, $cmd);
	echo $response; 
	fclose($fp);
}

function change_acl_node_type()
{
	$node_id = $this->input->post('node_id');
	$value = $this->input->post('value');
	$this->customer_model->change_acl_node_type($node_id, $value);

	//relaod acl
	$fp = $this->esl->event_socket_create($this->esl->ESL_host, $this->esl->ESL_port, $this->esl->ESL_password);
	$cmd = "api reloadacl";
	$response = $this->esl->event_socket_request($fp, $cmd);
	echo $response; 
	fclose($fp);
}

//**************************** CUSTOMER SIP ACCESS FUNCTION *************************************//

function sip_access($customer_id)
{
	$data['sip_access']     =   $this->customer_model->customer_sip_access($customer_id);
	$data['customer_id']    =   $customer_id;

	$data['page_name']		=	'customer_sip_access';
	$data['selected']		=	'sip_access';
	$data['sub_selected']   =   '';
	$data['page_title']		=	'CUSTOMER SIP CREDENTIALS';
	$data['main_menu']	    =	'';
	$data['sub_menu']	    =	'';
	$data['main_content']	=	'customers/sip_customer_view';
	$this->load->view('default/template',$data);
}

function new_sip_access($customer_id)
{
	$check = 0;
	do {
		$username = rand(1,999).rand(1,999);
		$check_username_existis = $this->customer_model->check_sip_username_existis($username);

		if($check_username_existis == 0)
		{
			$check = 1;
		}
	} while ($check == 0);

	$data['customer_id']    =   $customer_id;
	$data['username']       =   $username;
	$data['password']       =   rand(1,999).rand(1,999).rand(1,99);

	$data['page_name']		=	'new_sip_access';
	$data['selected']		=	'sip_access';
	$data['sub_selected']   =   '';
	$data['page_title']		=	'NEW SIP CREDENTIALS';
	$data['main_menu']	    =	'';
	$data['sub_menu']	    =	'';
	$data['main_content']	=	'customers/new_sip_view';
	$this->load->view('default/template',$data);
}

function insert_new_sip_access()
{
	$customer_id    =   $this->input->post('customer_id');
	$username       =   $this->input->post('username');
	$password       =   $this->input->post('password');

	$getdomain      =   $this->input->post('sip_ip');
	$explode = explode('|', $getdomain);

	$domain     = $explode[0];
	$sofia_id   = $explode[1];

	$this->customer_model->insert_new_sip_access($customer_id, $username, $password, $domain, $sofia_id);
}

/*
function edit_sip_access($id, $customer_id)
{
$data['sip_access']     =   $this->customer_model->single_sip_access_data($id);
$data['customer_id']    =   $customer_id;
$data['record_id']      =   $id;

$data['page_name']		=	'edit_sip_access';
$data['selected']		=	'sip_access';
$data['sub_selected']   =   '';
$data['page_title']		=	'UPDATE SIP CREDENTIALS';
$data['main_menu']	    =	'';
$data['sub_menu']	    =	'';
$data['main_content']	=	'customers/edit_sip_view';
$this->load->view('default/template',$data);
}

function update_sip_access()
{
$customer_id    =   $this->input->post('customer_id');
$record_id      =   $this->input->post('record_id');

$username       =   $this->input->post('username');
$old_username   =   $this->input->post('old_username');
$password       =   $this->input->post('password');
$domain         =   $this->input->post('sip_ip');

if($old_username != $username)
{
$check_username_availability = $this->customer_model->check_sip_username_existis($username);

if($check_username_availability == 0)
{
$this->customer_model->update_sip_access($record_id, $username, $password, $domain);
}
else
{
echo "username_not_available";
exit;
}
}
else
{
$this->customer_model->update_sip_access($record_id, $username, $password, $domain);
}
}*/

function delete_sip_access()
{
	$record_id      =   $this->input->post('record_id');

	$this->customer_model->delete_sip_access($record_id);
}

// **************************** CDR FUNCTIONS ****************************//
function customer_cdr($customer_id)
{
	$filter_display_results = 'min';

	//this is defualt start and end time  
	$startTime = time() - 86400; //last 24hrs 
	$endTime = time();

	//for filter & search
	$filter_date_from   = date('Y-m-d H:i:s', $startTime);
	$filter_date_to     = date('Y-m-d H:i:s', $endTime);
	$filter_phonenum    = '';
	$filter_caller_ip   = '';
	$filter_gateways    = '';
	$filter_call_type   = '';
	$search             = '';

	$msg_records_found = "Records Found";

	if($this->input->get('searchFilter'))
	{
		$filter_date_from       = $this->input->get('filter_date_from');
		$filter_date_to         = $this->input->get('filter_date_to');
		$filter_phonenum        = $this->input->get('filter_phonenum');
		$filter_caller_ip       = $this->input->get('filter_caller_ip');
		$filter_gateways        = $this->input->get('filter_gateways');
		$filter_call_type       = $this->input->get('filter_call_type');
		$filter_display_results = $this->input->get('filter_display_results');
		$search                 = $this->input->get('searchFilter');
		$msg_records_found      = "Records Found Based On Your Search Criteria";
	}

	if($filter_display_results   == '')
	{
		$filter_display_results   = 'min';
	}

	if($filter_display_results != 'min' && $filter_display_results != 'sec')
	{
		$filter_display_results   = 'min';
	}

	if($filter_date_from == '')
	{
		$filter_date_from   = date('Y-m-d H:i:s', $startTime);
	}
	else
	{
		if (!checkdateTime($filter_date_from))
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
	}

	if($filter_date_to == '')
	{
		$filter_date_to     = date('Y-m-d H:i:s', $endTime);
	}
	else
	{
		if (!checkdateTime($filter_date_to))
		{
			$filter_date_to   = date('Y-m-d H:i:s', $endTime);
		}
	}

	$data['filter_date_from']           = $filter_date_from;
	$data['filter_date_to']             = $filter_date_to;
	$data['filter_phonenum']            = $filter_phonenum;
	$data['filter_caller_ip']           = $filter_caller_ip;
	$data['filter_gateways']            = $filter_gateways;
	$data['filter_call_type']           = $filter_call_type;
	$data['filter_display_results']     = $filter_display_results;

	//for pagging set information
	$this->load->library('pagination');
	$config['per_page'] = '20';
	$config['base_url'] = base_url().'customers/customer_cdr/'.$customer_id.'/?searchFilter='.$search.'&filter_date_from='.$filter_date_from.'&filter_date_to='.$filter_date_to.'&filter_phonenum='.$filter_phonenum.'&filter_caller_ip='.$filter_caller_ip.'&filter_gateways='.$filter_gateways.'&filter_call_type='.$filter_call_type.'&filter_display_results='.$filter_display_results.' ';
	$config['page_query_string'] = TRUE;

	$config['num_links'] = 6;

	$config['cur_tag_open'] = '<span class="current">';
	$config['cur_tag_close'] = '</span> ';

	$config['next_link'] = 'next';
	$config['next_tag_open'] = '<span class="next-site">';
	$config['next_tag_close'] = '</span>';

	$config['prev_link'] = 'previous';
	$config['prev_tag_open'] = '<span class="prev-site">';
	$config['prev_tag_close'] = '</span>';

	$config['first_link'] = 'first';
	$config['last_link'] = 'last';

	$data['count'] = $this->customer_model->customer_cdr_count($customer_id, $filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_gateways, $filter_call_type);
	$config['total_rows'] = $data['count'];

	if(isset($_GET['per_page']))
	{
		$config['uri_segment'] = $_GET['per_page'];
	}
	else
	{
		$config['uri_segment'] = '';
	}

	$this->pagination->initialize($config);

	$data['msg_records_found'] = "".$data['count']."&nbsp;".$msg_records_found."";

	$data['cdr']            =   $this->customer_model->customer_cdr($config['per_page'],$config['uri_segment'], $customer_id, $filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_gateways, $filter_call_type);

	$data['customer_id']    =   $customer_id;

	$data['page_name']		=	'customer_cdr_data';
	$data['selected']		=	'customers_cdr';
	$data['sub_selected']   =   '';
	$data['page_title']		=	'CUSTOMER CDR';
	$data['main_menu']	    =	'';
	$data['sub_menu']	    =	'';
	$data['main_content']	=	'customers/cdr_customer_view';
	$this->load->view('default/template',$data);
}

//**************************** MANAGE BALANCE ************************************//
function manage_balance($customer_id)
{
	$data['history']        =   $this->customer_model->customer_balance_history($customer_id);
	$data['customer_id']    =   $customer_id;

	$data['page_name']		=	'customer_manage_balance';
	$data['selected']		=	'manage_balance';
	$data['sub_selected']   =   '';
	$data['page_title']		=	'MANAGE CUSTOMER BALANCE';
	$data['main_menu']	    =	'';
	$data['sub_menu']	    =	'';
	$data['main_content']	=	'customers/balance_customer_view';
	$this->load->view('default/template',$data);
}

function add_deduct_balance()
{
	$customer_id    =   $this->input->post('customer_id');
	$balance        =   $this->input->post('balance');
	$action         =   $this->input->post('action');
	$current_balance = $this->customer_model->customer_any_cell($customer_id, 'customer_balance');

	$insert = $this->customer_model->add_deduct_balance($customer_id, $balance, $action, $current_balance);

	$latest = $this->customer_model->customer_balance_history_single($insert);
	$row = $latest->row();

	echo '<tr class="main_text"><td align="center">'.date('Y-m-d', $row->date).'</td>
		<td align="center">'.$row->balance.'</td>
		<td align="center">'.strtoupper($row->action).'</td></tr>';
}

function my_account()
{
	$data['page_name']		=	'my_account';
	$data['selected']		=	'';
	$data['sub_selected']   =   '';
	$data['page_title']		=	'MY ACCOUNT';
	$data['main_menu']	    =	'default/main_menu/main_menu';
	$data['sub_menu']	    =	'';
	$data['main_content']	=	'my_account';
	$this->load->view('default/template',$data);
}

function update_my_account()
{
	$data['chng_username']  = $this->input->post('chng_username');
	$data['username']       = $this->db->escape($this->input->post('username'));
	$data['old_username']   = $this->db->escape($this->input->post('old_username'));

	$check_username_availability_count = 0;

	if($data['chng_username'] == 'Y') //if user want to change username
	{
		if($data['username'] != $data['old_username']) //if entered username not equal to previous username 
		{
			$check_username_availability = $this->manage_accounts_model->check_username_availability($data['username']);
			if($check_username_availability->num_rows() > 0) //username already in use
			{
				$check_username_availability_count = 1;
			}
		}
	}
	$data['chng_password']  = $this->input->post('chng_password');
	$data['pass']           = $this->input->post('password');
	$data['password']       = md5($this->input->post('password'));

	if($check_username_availability_count == 1)
	{
		$this->session->set_flashdata('error','Username already taken. Try different username');
	}
	else
	{
		if($data['chng_username'] == 'Y') //if user want to change username
		{
			if($data['username'] != $data['old_username']) //if entered username not equal to previous username 
			{
				$this->customer_model->update_user_username($data);
			}
		}

		if($data['chng_password'] == 'Y') //if user want to change password
		{
			$this->customer_model->update_user_password($data);
		}

		if($data['chng_username'] == 'Y') //if user want to change username
		{
			if($data['username'] != $data['old_username']) //if entered username not equal to previous username 
			{
				//update session variable 
				$data = array(
					'username' => $this->input->post('username')
					);
				$this->session->set_userdata($data);
			}
		}



		$this->session->set_flashdata('success','Information updated successfully');
	}
}

/*******************BILLING FUNCTION ***************************/
    function invoices($customer_id)
    {
        $data['customer_id']    =   $customer_id;
        
        //for filter & search
        $filter_date_from       = '';
        $filter_date_to         = '';
        $filter_customers       = $customer_id;
        $filter_status          = '';
        $search                 = '';

        $msg_records_found = "Records Found";

        if($this->input->get('searchFilter'))
        {
            $filter_date_from       = $this->input->get('filter_date_from');
            $filter_date_to         = $this->input->get('filter_date_to');
            $filter_status          = $this->input->get('filter_status');
            $search                 = $this->input->get('searchFilter');
            $msg_records_found      = "Records Found Based On Your Search Criteria";
        }

        if($filter_date_from != '')
        {
            if (!checkdateTime($filter_date_from))
            {
                //$filter_date_from   = '';
            }
        }

        if($filter_date_to != '')
        {
            if (!checkdateTime($filter_date_to))
            {
                //$filter_date_to   = '';
            }
        }

        $data['filter_date_from']           = $filter_date_from;
        $data['filter_date_to']             = $filter_date_to;
        $data['filter_status']              = $filter_status;
        
        //for pagging set information
        $this->load->library('pagination');
        $config['per_page'] = '20';
        $config['base_url'] = base_url().'customers/invoices/'.$customer_id.'/?searchFilter='.$search.'&filter_date_from='.$filter_date_from.'&filter_date_to='.$filter_date_to.'&filter_status='.$filter_status.'';
        $config['page_query_string'] = TRUE;

        $config['num_links'] = 6;

        $config['cur_tag_open'] = '<span class="current">';
        $config['cur_tag_close'] = '</span> ';

        $config['next_link'] = 'next';
        $config['next_tag_open'] = '<span class="next-site">';
        $config['next_tag_close'] = '</span>';

        $config['prev_link'] = 'previous';
        $config['prev_tag_open'] = '<span class="prev-site">';
        $config['prev_tag_close'] = '</span>';

        $config['first_link'] = 'first';
        $config['last_link'] = 'last';

        $data['count'] = $this->billing_model->get_invoices_count($filter_date_from, $filter_date_to, $filter_customers, $filter_billing_type = '', $filter_status);
        $config['total_rows'] = $data['count'];

        if(isset($_GET['per_page']))
        {
            if(is_numeric($_GET['per_page']))
            {
                $config['uri_segment'] = $_GET['per_page'];
            }
            else
            {
                $config['uri_segment'] = '';
            }
        }
        else
        {
            $config['uri_segment'] = '';
        }

        $this->pagination->initialize($config);

        $data['msg_records_found'] = "".$data['count']."&nbsp;".$msg_records_found."";

        $data['invoices']       =   $this->billing_model->get_invoices($config['per_page'],$config['uri_segment'],$filter_date_from, $filter_date_to, $filter_customers, $filter_billing_type = '', $filter_status);
        
        $data['page_name']		=	'customer_invoices';
        $data['selected']		=	'billing';
        $data['sub_selected']   =   '';
        $data['page_title']		=	'INVOICES';
        $data['main_menu']	    =	'';
        $data['sub_menu']	    =	'';
        $data['main_content']	=	'customers/invoices_view';
        $this->load->view('default/template',$data);
    }
}