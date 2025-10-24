<?php

namespace App\Controllers;

use App\Controllers\BaseEntityMaintenance;
use App\Models\DriverModel;

/**
 * Driver Maintenance Controller
 *
 * Extends BaseEntityMaintenance with driver-specific logic.
 * Most CRUD operations inherited from base class.
 *
 * @author Tony Lyle
 * @version 2.0 - Refactored to use base template
 */
class DriverMaintenance extends BaseEntityMaintenance
{
    // ==================== REQUIRED IMPLEMENTATIONS ====================

    protected function getEntityName(): string
    {
        return 'Driver';
    }

    protected function getEntityKey(): string
    {
        return 'DriverKey';
    }

    protected function getMenuPermission(): string
    {
        return 'mnuDriverMaint';
    }

    protected function getEntityModel()
    {
        if ($this->entityModel === null) {
            $this->entityModel = new DriverModel();
        }

        $customerDb = $this->getCurrentDatabase();
        if ($customerDb && $this->entityModel->db) {
            $this->entityModel->db->setDatabase($customerDb);
        }

        return $this->entityModel;
    }

    protected function getFormFields(): array
    {
        return [
            // Basic Information
            'DriverID' => [
                'type' => 'text',
                'label' => 'Driver ID',
                'maxlength' => 9,
                'section' => 'basic'
            ],
            'FirstName' => [
                'type' => 'text',
                'label' => 'First Name',
                'required' => true,
                'maxlength' => 15,
                'section' => 'basic'
            ],
            'MiddleName' => [
                'type' => 'text',
                'label' => 'Middle Name',
                'maxlength' => 15,
                'section' => 'basic'
            ],
            'LastName' => [
                'type' => 'text',
                'label' => 'Last Name',
                'required' => true,
                'maxlength' => 15,
                'section' => 'basic'
            ],
            'Email' => [
                'type' => 'email',
                'label' => 'Email',
                'maxlength' => 50,
                'section' => 'basic'
            ],
            'BirthDate' => [
                'type' => 'date',
                'label' => 'Birth Date',
                'section' => 'basic',
                'nullDate' => '1899-12-30'
            ],
            'StartDate' => [
                'type' => 'date',
                'label' => 'Start Date',
                'section' => 'basic',
                'nullDate' => '1899-12-30'
            ],
            'EndDate' => [
                'type' => 'date',
                'label' => 'End Date',
                'section' => 'basic',
                'nullDate' => '1899-12-30',
                'help' => 'Leave empty for active drivers'
            ],
            'Active' => [
                'type' => 'checkbox',
                'label' => 'Active',
                'section' => 'basic',
                'help' => 'Auto-set by End Date'
            ],

            // License & Medical
            'LicenseNumber' => [
                'type' => 'text',
                'label' => 'License Number',
                'maxlength' => 15,
                'section' => 'license'
            ],
            'LicenseState' => [
                'type' => 'text',
                'label' => 'License State',
                'maxlength' => 2,
                'section' => 'license',
                'uppercase' => true
            ],
            'LicenseExpires' => [
                'type' => 'date',
                'label' => 'License Expires',
                'section' => 'license',
                'nullDate' => '1899-12-30'
            ],
            'PhysicalDate' => [
                'type' => 'date',
                'label' => 'Physical Date',
                'section' => 'license',
                'nullDate' => '1899-12-30'
            ],
            'PhysicalExpires' => [
                'type' => 'date',
                'label' => 'Physical Expires',
                'section' => 'license',
                'nullDate' => '1899-12-30'
            ],
            'MVRDue' => [
                'type' => 'date',
                'label' => 'MVR Due',
                'section' => 'license',
                'nullDate' => '1899-12-30'
            ],
            'MedicalVerification' => [
                'type' => 'checkbox',
                'label' => 'Medical Verification',
                'section' => 'license'
            ],

            // Driver Characteristics
            'DriverType' => [
                'type' => 'select',
                'label' => 'Driver Type',
                'section' => 'characteristics',
                'options' => [
                    'F' => 'Full-time',
                    'P' => 'Part-time',
                    'C' => 'Contractor'
                ],
                'default' => 'F'
            ],
            'DriverSpec' => [
                'type' => 'select',
                'label' => 'Driver Spec',
                'section' => 'characteristics',
                'options' => [
                    'OTH' => 'Other',
                    'HAZ' => 'Hazmat',
                    'TNK' => 'Tanker'
                ],
                'default' => 'OTH'
            ],
            'FavoriteRoute' => [
                'type' => 'text',
                'label' => 'Favorite Route',
                'maxlength' => 50,
                'section' => 'characteristics'
            ],
            'TWIC' => [
                'type' => 'checkbox',
                'label' => 'TWIC Certified',
                'section' => 'characteristics'
            ],
            'CoilCert' => [
                'type' => 'checkbox',
                'label' => 'Coil Certified',
                'section' => 'characteristics'
            ],

            // Pay Information
            'PayType' => [
                'type' => 'select',
                'label' => 'Pay Type',
                'section' => 'pay',
                'options' => [
                    'P' => 'Percentage',
                    'M' => 'Mileage',
                    'H' => 'Hourly'
                ],
                'default' => 'P'
            ],
            'CompanyLoadedPay' => [
                'type' => 'number',
                'label' => 'Loaded Pay',
                'step' => '0.001',
                'section' => 'pay',
                'default' => 0.000
            ],
            'CompanyEmptyPay' => [
                'type' => 'number',
                'label' => 'Empty Pay',
                'step' => '0.001',
                'section' => 'pay',
                'default' => 0.000
            ],
            'CompanyTarpPay' => [
                'type' => 'number',
                'label' => 'Tarp Pay',
                'step' => '0.01',
                'section' => 'pay',
                'default' => 0.00
            ],
            'CompanyStopPay' => [
                'type' => 'number',
                'label' => 'Stop Pay',
                'step' => '0.01',
                'section' => 'pay',
                'default' => 0.00
            ],
            'WeeklyCash' => [
                'type' => 'number',
                'label' => 'Weekly Cash',
                'step' => '0.01',
                'section' => 'pay',
                'default' => 0.00
            ],
            'CardException' => [
                'type' => 'checkbox',
                'label' => 'Card Exception',
                'section' => 'pay'
            ],

            // Company Driver Info
            'CompanyID' => [
                'type' => 'number',
                'label' => 'Company ID',
                'section' => 'company',
                'default' => 3
            ],
            'CompanyDriver' => [
                'type' => 'checkbox',
                'label' => 'Company Driver',
                'section' => 'company'
            ],
            'EOBR' => [
                'type' => 'checkbox',
                'label' => 'EOBR (Electronic Logging)',
                'section' => 'company'
            ],
            'EOBRStart' => [
                'type' => 'date',
                'label' => 'EOBR Start Date',
                'section' => 'company',
                'nullDate' => '1899-12-30'
            ],
            'ARCNC' => [
                'type' => 'date',
                'label' => 'AR CNC Date',
                'section' => 'company',
                'nullDate' => '1899-12-30'
            ],
            'TXCNC' => [
                'type' => 'date',
                'label' => 'TX CNC Date',
                'section' => 'company',
                'nullDate' => '1899-12-30'
            ]
        ];
    }

    protected function getNewEntityTemplate(): array
    {
        return [
            'DriverKey' => 0,
            'DriverID' => '',
            'FirstName' => 'New',
            'MiddleName' => '',
            'LastName' => 'Driver',
            'BirthDate' => null,
            'LicenseNumber' => '',
            'LicenseState' => '',
            'LicenseExpires' => null,
            'PhysicalDate' => null,
            'PhysicalExpires' => null,
            'StartDate' => date('Y-m-d'),
            'EndDate' => null,
            'Active' => 1,
            'FavoriteRoute' => '',
            'DriverType' => 'F',
            'Email' => '',
            'TWIC' => 0,
            'CoilCert' => 0,
            'CompanyID' => 3,
            'ARCNC' => null,
            'TXCNC' => null,
            'CompanyDriver' => 0,
            'EOBR' => 0,
            'EOBRStart' => null,
            'WeeklyCash' => 0.00,
            'CardException' => 0,
            'DriverSpec' => 'OTH',
            'MedicalVerification' => 0,
            'MVRDue' => null,
            'CompanyLoadedPay' => 0.000,
            'CompanyEmptyPay' => 0.000,
            'PayType' => 'P',
            'CompanyTarpPay' => 0.00,
            'CompanyStopPay' => 0.00
        ];
    }

    // ==================== DRIVER-SPECIFIC OVERRIDE ====================

    /**
     * Save driver (create or update)
     * Override to handle driver-specific 33-parameter stored procedure
     */
    public function save()
    {
        $this->requireAuth();
        $this->requireMenuPermission($this->getMenuPermission());

        $db = $this->getCustomerDb();

        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'first_name' => 'required|max_length[15]',
            'last_name' => 'required|max_length[15]',
            'middle_name' => 'permit_empty|max_length[15]',
            'driver_id' => 'permit_empty|max_length[9]',
            'email' => 'permit_empty|valid_email|max_length[50]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Business rule validation: Active status must match End Date
        $endDate = $this->request->getPost('end_date');
        $hasEndDate = !empty($endDate);
        $isActiveChecked = $this->request->getPost('active') ? true : false;

        if (!$isActiveChecked && !$hasEndDate) {
            return redirect()->back()->withInput()
                ->with('error', 'An inactive driver must have an End Date. Please enter an End Date to deactivate this driver.');
        }

        if ($isActiveChecked && $hasEndDate) {
            return redirect()->back()->withInput()
                ->with('error', 'An active driver cannot have an End Date. Please remove the End Date or uncheck Active to deactivate this driver.');
        }

        $driverKey = intval($this->request->getPost('driver_key') ?? 0);
        $isNewDriver = ($driverKey == 0);

        try {
            // Map form fields to driver data (33 parameters for spDriver_Save)
            $driverData = [
                'DriverKey' => $driverKey,
                'DriverID' => $this->request->getPost('driver_id'),
                'FirstName' => $this->request->getPost('first_name'),
                'MiddleName' => $this->request->getPost('middle_name'),
                'LastName' => $this->request->getPost('last_name'),
                'BirthDate' => $this->request->getPost('birth_date'),
                'LicenseNumber' => $this->request->getPost('license_number'),
                'LicenseState' => $this->request->getPost('license_state'),
                'LicenseExpires' => $this->request->getPost('license_expires'),
                'PhysicalDate' => $this->request->getPost('physical_date'),
                'PhysicalExpires' => $this->request->getPost('physical_expires'),
                'StartDate' => $this->request->getPost('start_date'),
                'EndDate' => $this->request->getPost('end_date'),
                'Active' => $this->request->getPost('active') ? 1 : 0,
                'FavoriteRoute' => $this->request->getPost('favorite_route'),
                'DriverType' => $this->request->getPost('driver_type') ?? 'F',
                'Email' => $this->request->getPost('email'),
                'TWIC' => $this->request->getPost('twic') ? 1 : 0,
                'CoilCert' => $this->request->getPost('coil_cert') ? 1 : 0,
                'CompanyID' => intval($this->request->getPost('company_id') ?? 3),
                'ARCNC' => $this->request->getPost('arcnc'),
                'TXCNC' => $this->request->getPost('txcnc'),
                'CompanyDriver' => $this->request->getPost('company_driver') ? 1 : 0,
                'EOBR' => $this->request->getPost('eobr') ? 1 : 0,
                'EOBRStart' => $this->request->getPost('eobr_start'),
                'WeeklyCash' => floatval($this->request->getPost('weekly_cash') ?? 0.00),
                'CardException' => $this->request->getPost('card_exception') ? 1 : 0,
                'DriverSpec' => $this->request->getPost('driver_spec') ?? 'OTH',
                'MedicalVerification' => $this->request->getPost('medical_verification') ? 1 : 0,
                'MVRDue' => $this->request->getPost('mvr_due'),
                'CompanyLoadedPay' => floatval($this->request->getPost('company_loaded_pay') ?? 0.00),
                'CompanyEmptyPay' => floatval($this->request->getPost('company_empty_pay') ?? 0.00),
                'PayType' => $this->request->getPost('pay_type') ?? 'P',
                'CompanyTarpPay' => floatval($this->request->getPost('company_tarp_pay') ?? 0.00),
                'CompanyStopPay' => floatval($this->request->getPost('company_stop_pay') ?? 0.00)
            ];

            if ($this->getEntityModel()->saveDriver($driverData)) {
                if ($isNewDriver) {
                    $fullName = $driverData['LastName'] . ', ' . $driverData['FirstName'];
                    $newDriver = $this->getEntityModel()->searchDriverByName($fullName);

                    if ($newDriver && isset($newDriver['DriverKey'])) {
                        $newDriverKey = $newDriver['DriverKey'];

                        $newNameKey = $this->getAddressModel()->createBlankAddress('DR');
                        if ($newNameKey > 0) {
                            $this->getAddressModel()->linkDriverAddress($newDriverKey, $newNameKey);
                        }

                        return redirect()->to('/safety/driver-maintenance/load/' . $newDriverKey)
                            ->with('success', 'Driver created successfully.');
                    }
                } else {
                    return redirect()->to('/safety/driver-maintenance/load/' . $driverKey)
                        ->with('success', 'Driver updated successfully.');
                }
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to save driver.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Driver maintenance save error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Database error occurred.');
        }
    }
}
