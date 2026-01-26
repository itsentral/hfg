<?php defined('BASEPATH') || exit('No direct script access allowed');

class Menu_generator
{
	protected $ci;
	protected $x; //Db Prefix
	protected $uri; //Current uri string
	protected $user_id;
	protected $is_admin;

	public function __construct()
	{
		$this->ci = &get_instance();

		$this->x = $this->ci->db->dbprefix;
		$this->uri = '/' . $this->ci->uri->uri_string() . '/';

		$this->ci->load->helper('app');
		//$this->ci->load->model('menus/users_model');
		$this->ci->load->library('users/auth');
		$this->user_id 	= $this->ci->auth->user_id();
		$this->is_admin = $this->ci->auth->is_admin();
	}

	public function build_menus($type = 1)
	{
		$auth = $this->get_auth_permission($this->user_id);
		if (!$auth) {
			$auth = array(NULL);
		}

		if ($type != 1) {
			return ""; // kalau ada menu lain nanti bisa dibuat juga
		}

		$menu = $this->ci->db->select("t1.*")
			->from("{$this->x}menus as t1")
			->join("{$this->x}menus as t2", "t1.id = t2.parent_id", "left")
			->where("t1.parent_id", 0)
			->where("t1.group_menu", $type)
			->where("t1.status", 1)
			->group_by("t1.id")
			->order_by("t1.order", "ASC")
			->get()
			->result();

		// ============================
		// START BERRY MENU
		// ============================
		$html = "<ul class='pc-navbar'>";

		// Dashboard
		$dashActive = (strpos($this->uri, '/dashboard/') !== FALSE || $this->uri == site_url() || $this->uri == site_url('/')) ? "active" : "";
		$html .= "
        <li class='pc-item {$dashActive}'>
            <a href='" . site_url() . "' class='pc-link'>
                <span class='pc-micon'><i class='ti ti-dashboard'></i></span>
                <span class='pc-mtext'>Dashboard</span>
            </a>
        </li>
    ";

		if (is_array($menu) && count($menu)) {
			foreach ($menu as $rw) {
				$id        = $rw->id;
				$title     = $rw->title;
				$titleID   = strtolower(str_replace(' ', '', $rw->title)) . $id;
				$link      = $rw->link;
				$icon      = $rw->icon ?: "fa fa-circle-o";
				$target    = $rw->target;

				// Ambil submenu level 2
				$submenu = $this->ci->db->select("t1.*")
					->from("{$this->x}menus as t1")
					->where("t1.parent_id", $id)
					->where("t1.group_menu", $type)
					->where("t1.status", 1);

				if (!$this->is_admin) {
					$submenu = $submenu->where_in("t1.permission_id", $auth);
				}

				$submenu = $submenu->group_by("t1.id")
					->order_by("t1.order", "ASC")
					->get()
					->result();

				// ============================
				// MENU TANPA SUBMENU
				// ============================
				if (count($submenu) == 0) {

					// skip kalau tidak punya auth
					if ($link != "#") {
						if (!in_array($rw->permission_id, $auth) && $this->is_admin == FALSE) {
							continue;
						}

						$active = "";
						if (strpos($this->uri, '/' . $link . '/') !== FALSE) {
							$active = "active";
						}

						$url = ($link == '#') ? '#!' : site_url($link);
						$targetAttr = ($target == '_blank') ? "target='_blank'" : "";

						$html .= "
                        <li class='pc-item {$active}'>
                            <a href='{$url}' class='pc-link' {$targetAttr}>
                                <span class='pc-micon'><i class='{$icon}'></i></span>
                                <span class='pc-mtext'>" . ucwords($title) . "</span>
                            </a>
                        </li>
                    ";
					}

					continue;
				}

				// ============================
				// MENU DENGAN SUBMENU
				// ============================
				$parentActive = "";
				foreach ($submenu as $sub) {
					if (strpos($this->uri, '/' . $sub->link . '/') !== FALSE) {
						$parentActive = "active pc-trigger"; // pc-trigger = submenu terbuka
						break;
					}
				}

				$html .= "
                <li class='pc-item pc-hasmenu {$titleID}head {$parentActive}'>
                    <a href='#!' class='pc-link'>
                        <span class='pc-micon'><i class='{$icon}'></i></span>
                        <span class='pc-mtext'>" . ucwords($title) . "</span>
                        <span class='pc-arrow'><i data-feather='chevron-right'></i></span>
                    </a>
                    <ul class='pc-submenu'>
            ";

				// ============================
				// SUBMENU LEVEL 2
				// ============================
				foreach ($submenu as $sub) {
					$subid      = $sub->id;
					$subtitle   = $sub->title;
					$sublink    = $sub->link;
					$subicon    = $sub->icon ?: "fa fa-circle-o";
					$subtarget  = $sub->target;

					// Ambil submenu level 3
					$submenusub = $this->ci->db->select("t1.*")
						->from("{$this->x}menus as t1")
						->where("t1.parent_id", $subid)
						->where("t1.group_menu", $type)
						->where("t1.status", 1);

					if (!$this->is_admin) {
						$submenusub = $submenusub->where_in("t1.permission_id", $auth);
					}

					$submenusub = $submenusub->group_by("t1.id")
						->order_by("t1.order", "ASC")
						->get()
						->result();

					// ============================
					// SUBMENU LEVEL 2 TANPA LEVEL 3
					// ============================
					if (count($submenusub) == 0) {
						if ($sublink != "#") {
							if (!in_array($rw->permission_id, $auth) && $this->is_admin == FALSE) {
								continue;
							}

							$active = "";
							if (strpos($this->uri, '/' . $sublink . '/') !== FALSE) {
								$active = "active";
							}

							$url = ($sublink == '#') ? '#!' : site_url($sublink);
							$targetAttr = ($subtarget == '_blank') ? "target='_blank'" : "";

							$html .= "
                            <li class='pc-item {$active}'>
                                <a class='pc-link' href='{$url}' {$targetAttr}>
                                    <span class='pc-micon'><i class='{$subicon}'></i></span>
                                    <span class='pc-mtext'>" . ucwords($subtitle) . "</span>
                                </a>
                            </li>
                        ";
						}
						continue;
					}

					// ============================
					// SUBMENU LEVEL 2 PUNYA LEVEL 3
					// ============================
					$subParentActive = "";
					foreach ($submenusub as $subsub) {
						if (strpos($this->uri, '/' . $subsub->link . '/') !== FALSE) {
							$subParentActive = "active pc-trigger";
							break;
						}
					}

					$html .= "
                    <li class='pc-item pc-hasmenu {$subParentActive}'>
                        <a href='#!' class='pc-link'>
                            <span class='pc-micon'><i class='{$subicon}'></i></span>
                            <span class='pc-mtext'>" . ucwords($subtitle) . "</span>
                            <span class='pc-arrow'><i data-feather='chevron-right'></i></span>
                        </a>
                        <ul class='pc-submenu'>
                ";

					// SUBMENU LEVEL 3
					foreach ($submenusub as $subsub) {
						$subtitlesub = $subsub->title;
						$sublinksub  = $subsub->link;
						$subiconsub  = $subsub->icon ?: "fa fa-circle-o";
						$subtargetsub = $subsub->target;

						$active = (strpos($this->uri, '/' . $sublinksub . '/') !== FALSE) ? "active" : "";

						$url = ($sublinksub == '#') ? '#!' : site_url($sublinksub);
						$targetAttr = ($subtargetsub == '_blank') ? "target='_blank'" : "";

						$html .= "
                        <li class='pc-item {$active}'>
                            <a class='pc-link' href='{$url}' {$targetAttr}>
                                <span class='pc-micon'><i class='{$subiconsub}'></i></span>
                                <span class='pc-mtext'>" . ucwords($subtitlesub) . "</span>
                            </a>
                        </li>
                    ";
					}

					$html .= "
                        </ul>
                    </li>
                ";
				}

				$html .= "
                    </ul>
                </li>
            ";
			}
		}

		$html .= "</ul>";

		return $html;
	}


	public function build_menus_OLD($type = 1)
	{
		$auth = $this->get_auth_permission($this->user_id);
		if (!$auth) {
			$auth = array(NULL);
		}

		if ($type == 1) {
			$menu = $this->ci->db->select("t1.*")
				->from("{$this->x}menus as t1")
				->join("{$this->x}menus as t2", "t1.id = t2.parent_id", "left")
				//->join("{$this->x}menus as t3","t2.id = t3.parent_id","left")
				->where("t1.parent_id", 0)
				->where("t1.group_menu", $type)
				->where("t1.status", 1)
				->group_by("t1.id")
				->order_by("t1.order", "ASC")
				->get()
				->result();

			$html = "<ul class='sidebar-menu'>
							<li class='header'></li>
	                        <li class='" . check_class('dashboard', TRUE) . "'>
	                            <a href='" . site_url() . "'>
	                                <i class='fa fa-dashboard'></i> <span>Dashboard</span>
	                            </a>
	                        </li>";

			if (is_array($menu) && count($menu)) {
				foreach ($menu as $rw) {
					$id 		= $rw->id;
					$title 		= $rw->title;
					$link 		= $rw->link;
					$icon 		= $rw->icon;
					$target 	= $rw->target;
					$submenu = $this->ci->db->select("t1.*")
						->from("{$this->x}menus as t1")
						->where("t1.parent_id", $id)
						->where("t1.group_menu", $type)
						->where("t1.status", 1);
					if (!$this->is_admin) {
						$submenu = $submenu->where_in("t1.permission_id", $auth);
					}
					$submenu = $submenu->group_by("t1.id")
						->group_by("t1.id")
						->order_by("t1.order", "ASC")
						->get()
						->result();
					//Jump to end_for point
					if (count($submenu) == 0) {
						if ($link != "#") {
							if (!in_array($rw->permission_id, $auth) && $this->is_admin == FALSE) {
								goto end_for;
							}
							$active = "";
							if (strpos($this->uri, '/' . $link . '/') !== FALSE) {
								$active = "active";
							}
							$html .= "<li class='{$active}'><a href='" . ($link == '#' ? '#' : site_url($link)) . "' " . ($target == '_blank' ? "target='_blank'" : "") . "><i class='{$icon}'></i> <span>" . ucwords($title) . "</span></a></li>";
						}
						goto end_for;
					}
					$active = "";
					foreach ($submenu as $sub) {
						if (strpos($this->uri, '/' . $sub->link . '/') !== FALSE) {
							$active = "active";
							break;
						}
					}
					$html .= "
            			  <li class='treeview {$active}'>
                      <a href='#'>
                        <i class='" . $icon . "'></i>
                        <span>" . ucwords($title) . "</span>
                        <span class='pull-right-container'>
						            	<i class='fa fa-angle-left pull-right'></i>
						          	</span>
                      </a>
                      <ul class='treeview-menu'>";

					//Make Sub Menu
					foreach ($submenu as $sub) {
						$subid 		= $sub->id;
						$subtitle 	= $sub->title;
						$sublink 	= $sub->link;
						$subicon 	= $sub->icon;
						$subtarget 	= $sub->target;
						$subtarget = "";
						if ($subtarget == '_blank') {
							$subtarget = "target='_blank'";
						}


						//Check current link
						if (strpos($this->uri, '/' . $sublink . '/') !== FALSE) {
							$active = "active";
						} else {
							$active = "";
						}
						$html .= "
						<li class='" . $active . "'>
							<a href='" . ($sublink == '#' ? '#' : site_url($sublink)) . "'" . " " . $subtarget . ">
								<i class='" . $subicon . "'></i>" . ucwords($subtitle) . "
							</a>
						</li>";
					}
					$html .= "
						</ul>
					</li>";

					//Jump Point
					end_for:
					//END FOREACH MENU
				}
				$html .= "
					</ul>";
				/*=================================================================================================================
===================================================================================================================
===================================================================================================================
*/
			}
		} else {
			//other menu
		}

		return $html;
	}

	protected function get_auth_permission($user_id = 0)
	{
		$role_permissions = $this->ci->users_model->select("permissions.id_permission")
			->join("user_groups", "users.id_user = user_groups.id_user")
			->join("group_permissions", "user_groups.id_group = group_permissions.id_group")
			->join("permissions", "group_permissions.id_permission = permissions.id_permission")
			->where("users.id_user", $user_id)
			->find_all();

		$user_permissions = $this->ci->users_model->select("permissions.id_permission")
			->join("user_permissions", "users.id_user = user_permissions.id_user")
			->join("permissions", "user_permissions.id_permission = permissions.id_permission")
			->where("users.id_user", $user_id)
			->find_all();

		$merge = array();
		if ($role_permissions) {
			foreach ($role_permissions as $key => $rp) {
				if (!in_array($rp->id_permission, $merge)) {
					$merge[] = $rp->id_permission;
				}
			}
		}

		if ($user_permissions) {
			foreach ($user_permissions as $key => $up) {
				if (!in_array($up->id_permission, $merge)) {
					$merge[] = $up->id_permission;
				}
			}
		}

		return $merge;
	}
}
