<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	/**
	 * Default method
	 */

	protected $role;

	public function __construct()
	{
	    parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->model('all_model');
		$this->load->library('session');

		if(!$this->session->userdata('logged_in')){
			redirect(base_url("login"));
		}

		if($this->session->userdata('admin')){
			$this->role = $this->session->userdata('admin');
		} 
	}

	public function index()
	{	
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}

		for ($i = 0; $i < 12; $i++) {
			$months[] = date("M Y", strtotime( date( 'Y-m-01' )." -$i months"));
		}

		$month = array_reverse($months);
		$nextMonth = date("M Y", strtotime( date( 'Y-m-01' )." next months"));

		for ($i=0; $i < 12; $i++) {
			if ($i == 11) {
				$count[] = $this->all_model->getTransaksiMonth($month[$i], $nextMonth)->row()->bulan;
			} else {
				$count[] = $this->all_model->getTransaksiMonth($month[$i], $month[$i+1])->row()->bulan;
			}
		}


		
		$data = [
			'menu' => count($this->all_model->getAll('menu')->result()),
			'meja' => count($this->all_model->getAll('meja')->result()),
			'trans' => count($this->all_model->getAll('transaksi')->result()),
			'success' => $this->session->flashdata('success'),
			'bulan' => json_encode($month),
			'jumlah' => json_encode($count),
			'totalTransaksi' => $count[11],
			'totalPenjualan' => $this->all_model->getTotalTransaksi($month[11], $nextMonth)->row()->total,
			'totalPorsi' => $this->all_model->getTotalPorsi($month[11], $nextMonth)->row()->total
		];

		// echo '<pre>';
		// echo $month[11];
		// var_dump($data);
		return view('admin.index' ,$data);
	}

	public function getMenu()
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		$menu = $this->all_model->getAll('menu')->result();
		$success = $this->session->flashdata('success');

		if($success != NULL) {
			$data = [
				'success' => $success,
				'menu' => $menu
			];
		} else {
			$data = [
				'menu' => $menu
			];
		}
		
		return view('admin.menu.index', $data);
	}

	public function getAddMenu()
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		$message = $this->session->flashdata('error');

		if($message != NULL) {
			$data = [
				'message' => $message
			];
			return view('admin.menu.add', $data);
		}

		return view('admin.menu.add');
	}

	public function getEditMenu($id)  
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		$menu = $this->all_model->getData('menu', ['id' => $id])->row();

		if (empty($menu)) {
			show_404();
		}

		$message = $this->session->flashdata('error');

		if($message != NULL) {
			$data = [
				'message' => $message,
				'menu' => $menu
			];
		} else {
			$data = [
				'menu' => $menu
			];
		}
		// var_dump($menu);
		return view('admin.menu.edit', $data);
	}

	public function getDetailMenu($id)
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		$menu = $this->all_model->getData('menu', ['id' => $id])->row();

		if (empty($menu)) {
			show_404();
		}

		$data = [
			'menu' => $menu
		];
		
		return view('admin.menu.detail', $data);
	}

	public function getEditMeja($id)  
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		$meja = $this->all_model->getData('meja', ['id' => $id])->row();

		if (empty($meja)) {
			show_404();
		}

		$error = $this->session->flashdata('error');

		if($error != NULL) {
			$data = [
				'error' => $error,
				'meja' => $meja
			];
		} else {
			$data = [
				'meja' => $meja
			];
		}
		// var_dump($menu);
		return view('admin.meja.edit', $data);
	}

	public function getDeleteMenu($id)
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		$menu = $this->all_model->getData('menu', ['id' => $id])->row();
		if (empty($menu)) {
			show_404();
		}
		@unlink('.'.$menu->foto);
		$this->all_model->deleteData('menu', ['id' => $id]);
		$this->session->set_flashdata('success', 'Data successfully deleted');
		redirect('admin/menu', 'refresh');
	}

	public function getMeja()
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		$meja = $this->all_model->getAll('meja')->result();
		$success = $this->session->flashdata('success');

		if($success != NULL) {
			$data = [
				'success' => $success,
				'meja' => $meja
			];
		} else {
			$data = [
				'meja' => $meja
			];
		}
		return view('admin.meja.index', $data);
	}

	public function getAddMeja()
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		return view('admin.meja.add');
	}

	public function getDeleteMeja($id)
	{
		if(!$this->role) {
			redirect('transaksi', 'refresh');
		}
		$meja = $this->all_model->getData('meja', ['id' => $id])->row();
		if (empty($meja)) {
			show_404();
		}
		@unlink('./assets/'.$meja->foto);

		$this->all_model->deleteData('meja', ['id' => $id]);
		$this->session->set_flashdata('success', 'Data successfully deleted');
		redirect('admin/meja', 'refresh');
	}

	public function getPrintMeja($id)
	{
		$meja = $this->all_model->getData('meja', ['id' => $id])->row();

		if (empty($meja)) {
			show_404();
		}

		$data = [
			'meja' => $meja
		];
		
		return view('admin.qrcode', $data);
	}


	public function postAddMeja()
	{
		$token = base64_encode("" . mt_rand());
		$this->load->library('ciqrcode');

		$config['cacheable']    = true; //boolean, the default is true
        $config['cachedir']     = './assets/'; //string, the default is application/cache/
        $config['errorlog']     = './assets/'; //string, the default is application/logs/
        $config['imagedir']     = './assets/qrcode/'; //direktori penyimpanan qr code
        $config['quality']      = true; //boolean, the default is true
        $config['size']         = '1024'; //interger, the default is 1024
        $config['black']        = array(224,255,255); // array, default is array(255,255,255)
        $config['white']        = array(70,130,180); // array, default is array(0,0,0)
        $this->ciqrcode->initialize($config);
 
        $image_name=$token.'.png'; //buat name dari qr code sesuai dengan nim
 
        $params['data'] = $token; //data yang akan di jadikan QR CODE
        $params['level'] = 'H'; //H=High
        $params['size'] = 20;
        $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/qrcode/
		$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

		$data = [
			'no_meja' => $this->input->post('no_meja'),
			'deskripsi' => $this->input->post('deskripsi'),
			'status' => $this->input->post('status'),
			'qrcode' => $image_name,
			'kuota' => $this->input->post('jumlah'),
			'token' => $token,
		];
		
		$this->all_model->storeData('meja', $data);
		
		$this->session->set_flashdata('success', 'Data saved successfully');
		redirect('admin/meja', 'refresh');
	}

	public function postEditMeja($id)
	{
		$meja = $this->all_model->getData('meja', ['id' => $id])->row();
		if (empty($meja)) {
			show_404();
		}

		$data = [
			'no_meja' => $this->input->post('no_meja'),
			'deskripsi' => $this->input->post('deskripsi'),
			'status' => $this->input->post('status'),
			'kuota' => $this->input->post('jumlah')
		];

		$this->all_model->updateData('meja', ['id' => $id], $data);
		$this->session->set_flashdata('success', 'Data saved successfully');
		redirect('admin/meja', 'refresh');
	}

	public function postAddMenu()
	{
		$config['upload_path']          = './uploads/';
        $config['allowed_types']        = 'gif|jpg|png';
		$config['max_size']             = 1000;
		$config['file_name']    		= base64_encode("" . mt_rand());
        // $config['max_width']            = 1024;
		// $config['max_height']           = 768;
		
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload('photo'))
        {
			$error = array('error' => $this->upload->display_errors());
				var_dump($error);
			// redirect('admin/menu/add', 'refresh');
			// $this->load->view('upload_form', $error);
        }
        else
        {

			$data = [
				'foto' => '/uploads/'.$this->upload->data()['file_name'],
				'nama_menu' => $this->input->post('name'),
				'deskripsi' => $this->input->post('deskripsi'),
				'kategori' => $this->input->post('category'),
				'harga' => $this->input->post('price'),
				'status' => $this->input->post('status'),
			];

			$this->all_model->storeData('menu', $data);

			$this->session->set_flashdata('success', 'Data saved successfully');
			redirect('admin/menu', 'refresh');
            // $this->load->view('upload_success', $data);
        }
		// $this->redirect('admin/menu', 'refresh');
	}

	public function postEditMenu($id)
	{
		$menu = $this->all_model->getData('menu', ['id' => $id])->row();
		if (empty($menu)) {
			show_404();
		}

		$data = [
			'nama_menu' => $this->input->post('name'),
			'deskripsi' => $this->input->post('deskripsi'),
			'kategori' => $this->input->post('category'),
			'harga' => $this->input->post('price'),
			'status' => $this->input->post('status'),
		];

		if (!empty($_FILES['photo']['name'])) {
			$config['upload_path']          = './uploads/';
			$config['allowed_types']        = 'gif|jpg|png';
			$config['max_size']             = 1000;
			$config['file_name']    		= base64_encode("" . mt_rand());
			// $config['max_width']            = 1024;
			// $config['max_height']           = 768;
			
			$this->load->library('upload', $config);
			if (!$this->upload->do_upload('photo'))
			{
				$error = array('error' => $this->upload->display_errors());
				var_dump($error);
				// redirect('admin/menu/edit/'.$menu->id, 'refresh');
				// $this->load->view('upload_form', $error);
			} else {
				$data = [
					'foto' => '/uploads/'.$this->upload->data()['file_name'],
				];
				@unlink('.'.$menu->foto);
			}
		}

		$this->all_model->updateData('menu', ['id' => $id], $data);
		$this->session->set_flashdata('success', 'Data saved successfully');
		redirect('admin/menu', 'refresh');
	}
}
