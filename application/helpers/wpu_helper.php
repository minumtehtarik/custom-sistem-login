<?php

function is_logged_in()
{
    // gabisa manggil (this) karena ga masuk ke MVC nya CI
    // karena tidak mengenali struktur CI
    // if (!$this->session->userdata('email')) {  
    // redirect('auth');
    // }

    // makanya kita bikin instance CI baru di dalam helper ini
    // pake get_instance(); ini untuk memanggil library CI di dalam fungsi ini
    // kalo mau memanggil session yang ada di 
    $ci = get_instance();
    if (!$ci->session->userdata('email')) {
        redirect('auth');
    } else {
        $role_id = $ci->session->userdata('role_id');
        $menu = $ci->uri->segment(1);

        $queryMenu = $ci->db->get_where(
            'user_menu',
            ['menu' => $menu]
        )->row_array();

        $menu_id = $queryMenu['id'];

        $userAccess = $ci->db->get_where(
            'user_access_menu',
            [
                'role_id' => $role_id,
                'menu_id' => $menu_id
            ]
        );

        if ($userAccess->num_rows() < 1) {
            redirect('auth/blocked');
        }
    }
}


function check_access($role_id, $menu_id)
{
    $ci = get_instance();

    $ci->db->where('role_id', $role_id);
    $ci->db->where('menu_id', $menu_id);
    $result = $ci->db->get('user_access_menu');

    if ($result->num_rows() > 0) {
        return "checked='checked'";
    }

    // Contoh query lain
    // $ci->db->get_where('user_access_menu', ['role_id' => $role_id, 'menu_id' => $menu_id]);

}
