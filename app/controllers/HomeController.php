<?php
require_once __DIR__ . '/../models/MobilModel.php';

class HomeController {
    private $mobilModel;

    public function __construct() {
        $this->mobilModel = new MobilModel();
    }

    public function index() {
    $cars = $this->mobilModel->getAvailableCars();
    include __DIR__ . '/../views/home.php'; // Pastikan ini benar
    }

}
