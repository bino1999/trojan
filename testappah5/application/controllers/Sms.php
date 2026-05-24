<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		header('X-Frame-Options: SAMEORIGIN');
	}

	public function tester()
	{
		$data = array(
			'endpoint_url' => $this->input->get('endpoint_url', TRUE) ?: '',
			'token' => $this->input->get('token', TRUE) ?: '',
			'success_match' => $this->input->get('success_match', TRUE) ?: 'OK',
			'http_method' => $this->input->get('http_method', TRUE) ?: 'GET'
		);
		$this->load->view('sms/tester', $data);
	}

	public function send()
	{
		$this->output->set_content_type('application/json');

		$endpointUrl = trim($this->input->post('endpoint_url', TRUE) ?: '');
		$httpMethod = strtoupper(trim($this->input->post('http_method', TRUE) ?: 'GET'));
		$token = trim($this->input->post('token', FALSE) ?: '');
		$to = trim($this->input->post('to', TRUE) ?: '');
		$message = trim($this->input->post('message', FALSE) ?: '');
		$sender = trim($this->input->post('sender', TRUE) ?: '');
		$successMatch = trim($this->input->post('success_match', TRUE) ?: 'OK');

		if ($endpointUrl === '' || $to === '' || $message === '') {
			return $this->output->set_status_header(400)->set_output(json_encode(array(
				'success' => FALSE,
				'error' => 'Missing required fields: endpoint_url, to, message'
			)));
		}

		// Simple phone sanity check; adjust as needed
		if (!preg_match('/^[0-9+\-()\s]{7,20}$/', $to)) {
			return $this->output->set_status_header(422)->set_output(json_encode(array(
				'success' => FALSE,
				'error' => 'Invalid phone number format'
			)));
		}

		$ch = curl_init();

		$headers = array('Accept: application/json');
		if ($token !== '') {
			$headers[] = 'Authorization: Bearer ' . $token;
		}

		// Replace placeholders if present
		$finalUrl = $endpointUrl;
		$finalUrl = str_replace('{to}', rawurlencode($to), $finalUrl);
		$finalUrl = str_replace('{message}', rawurlencode($message), $finalUrl);
		$finalUrl = str_replace('{sender}', rawurlencode($sender), $finalUrl);

		// Build query/body
		$postFields = array();
		if (strpos($endpointUrl, '{to}') === FALSE || strpos($endpointUrl, '{message}') === FALSE) {
			$postFields['to'] = $to;
			$postFields['message'] = $message;
			if ($sender !== '') {
				$postFields['sender'] = $sender;
			}
		}

		if ($httpMethod === 'POST') {
			curl_setopt($ch, CURLOPT_URL, $finalUrl);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			if (!empty($postFields)) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
			}
		} else { // GET default
			if (!empty($postFields)) {
				$qs = http_build_query($postFields);
				$finalUrl = strpos($finalUrl, '?') === FALSE ? ($finalUrl . '?' . $qs) : ($finalUrl . '&' . $qs);
			}
			curl_setopt($ch, CURLOPT_URL, $finalUrl);
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		// If the provider uses HTTPS with proper certs this should remain TRUE. For testing you may disable.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		$responseBody = curl_exec($ch);
		$curlErr = curl_error($ch);
		$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$success = FALSE;
		if ($curlErr === '') {
			if ($successMatch !== '') {
				$success = (stripos($responseBody, $successMatch) !== FALSE);
			} else {
				$success = ($httpStatus >= 200 && $httpStatus < 300);
			}
		}

		return $this->output->set_output(json_encode(array(
			'success' => $success,
			'http_status' => $httpStatus,
			'endpoint' => $finalUrl,
			'error' => $curlErr ?: NULL,
			'body' => $responseBody
		)));
	}
}



