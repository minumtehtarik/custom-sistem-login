<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
    }

    public function index()
    {
        $data['title'] = 'My Profile';
        // kalo mau mengambil data dari user berdasarkan email yang ada di session
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        // echo 'Selamat datang ' . $data['user']['name'];

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/index', $data);
        $this->load->view('templates/footer');
    }

    public function edit()
    {
        $data['title'] = 'Edit Profil';
        // kalo mau mengambil data dari user berdasarkan email yang ada di session
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        // echo 'Selamat datang ' . $data['user']['name'];

        $this->form_validation->set_rules('name', 'Full Name', 'required|trim');

        // form validation
        if ($this->form_validation->run() == false) {

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $name = $this->input->post('name');
            $email = $this->input->post('email');

            // cek jika ada gambar yang akan diupload
            $upload_image = $_FILES['image']['name'];
            // var_dump($upload_image);
            // die;


            if ($upload_image) {
                $config['upload_path'] = './assets/img/profile/';
                $config['allowed_types'] = 'jpeg|jpg|png';
                $config['max_size']     = '2048'; // satuannya kilobyte

                $this->load->library('upload', $config);

                // kalo udah lolos semua kita upload filenya
                if ($this->upload->do_upload('image')) // <--sampai sini saja gambar sudah berhasil ke upload ke folder
                // tetapi kita juga perlu update nama image di tabel user
                // jadi kita ambil dulu nama gambar barunya

                // jangan khawatir, kalo nama gambarnya sama, Ci akan menambahkan angka di belakang namanya ^library^ jadi ga akan bentrok
                {

                    $old_image = $data['user']['image']; // data gambar lama
                    // sekarang kita cek gambar ini default bukan
                    if ($old_image != 'default.jpg') {
                        unlink(FCPATH . 'assets/img/profile/' . $old_image); // unlink untuk menghapus
                    }



                    $new_image = $this->upload->data('file_name');
                    $this->db->set('image', $new_image);
                } // kalo gagal
                else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . $this->upload->display_errors() . '</div>');
                    redirect('user');
                }
            }

            $this->db->set('name', $name);
            $this->db->where('email', $email);
            $this->db->update('user');


            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Your Profile has been updated</div>');
            redirect('user');
        }
    }


    public function changePassword()
    {
        $data['title'] = 'Change Password';
        // kalo mau mengambil data dari user berdasarkan email yang ada di session
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        echo 'Selamat datang ' . $data['user']['name'];

        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]');
        $this->form_validation->set_rules('new_password2', 'New Password', 'required|trim|min_length[3]|matches[new_password1]');

        if ($this->form_validation->run() == false) {

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/changepassword', $data);
            $this->load->view('templates/footer');
        } else {
            // cek current password sama atau tidak dengan yang di database
            $current_password = $this->input->post('current_password'); //tangkap dulu yang di tuliskan di kolom change password
            $new_password = $this->input->post('new_password1');
            // $current_password dengan $data password sama gak?
            // kalo ga sama !password_verify
            if (!password_verify($current_password, $data['user']['password'])) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Wrong Current Password!</div>');
                redirect('user/changepassword');
            } else {
                // cek lagi password yang baru ga boleh sama dengan password sebelumnya
                if ($current_password == $new_password) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                New password cannot be the same as current password!</div>');
                    redirect('user/changepassword');
                } else {
                    // jika berhasil change password
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT); //kita hash $new_password1
                    $this->db->set('password', $password_hash); // kita set password baru nya yang sudah di hash
                    $this->db->where('email', $this->session->userdata('email')); // where email = session userdata
                    $this->db->update('user'); // update tabel user


                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
                Password changed!</div>');
                    redirect('user/changepassword');
                }
            }
        }
    }
}
