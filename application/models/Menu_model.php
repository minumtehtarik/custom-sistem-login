<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Menu_model extends CI_Model
{
    public function getSubMenu()
    {
        // join dari tabel sub menu ke tabel menu
        $query = "SELECT `user_sub_menu`.*,`user_menu`.`menu`
        FROM `user_sub_menu` JOIN `user_menu`
        ON `user_sub_menu`.`menu_id` = `user_menu`.`id`
        ";
        // kita return untuk menampilkan semua datanya
        return $this->db->query($query)->result_array();
    }
}
