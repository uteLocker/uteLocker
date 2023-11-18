<?php

namespace App\Services\Admin\Clients;

use App\Classes\Common;
use App\Classes\CommonConstant;
use App\Classes\Files;
use App\Enums\ClientStatus;
use App\Exceptions\ApiException;
use App\Models\Client;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class ClientService extends BaseService
{
    public const FORM_PREFIX = 'client_';

    public function __construct()
    {
        parent::__construct(new Client());
    }

    /**
     * @throws ApiException
     */
    public function add(array $inputs, array $options = []): Model
    {
        $this->new();
        if ($options['isPrefix']) {
            $inputs = Common::mappingRemovePrefix($inputs, self::FORM_PREFIX);
        }
        $this->formatInputData($inputs);
        $this->setModelFields($inputs);

        $this->model->save();

        return $this->model;
    }

    /**
     * @throws ApiException
     */
    protected function  formatInputData(&$inputs): void
    {
        if (!empty($inputs['logo'])) {
            $inputs['logo'] = Files::upload($inputs['logo'], Files::CLIENT_LOGO_FOLDER, width: 300);
        }
        $inputs['name'] = $inputs['name'] ?? $this->model->name;
        $inputs['app_name'] = $inputs['app_name'] ?? $this->model->app_name;
        $inputs['email'] = $inputs['email'] ?? $this->model->email;
        $inputs['phone'] = $inputs['phone'] ?? $this->model->phone;
        $inputs['logo'] = $inputs['logo'] ?? $this->model->logo;
        $inputs['address'] = $inputs['address'] ?? $this->model->address;
        $inputs['website'] = $inputs['website'] ?? $this->model->website;
        $inputs['date_format'] = $inputs['date_format'] ?? $this->model->date_format;
        $inputs['locale'] = $inputs['locale'] ?? $this->model->locale;
        $inputs['time_format'] = $inputs['time_format'] ?? $this->model->time_format;
        $inputs['timezone'] = $inputs['timezone'] ?? $this->model->timezone;
        $inputs['status'] = $inputs['status'] ?? $this->model->status;
        $inputs['allow_signup'] = $inputs['allow_signup'] ?? $this->model->allow_signup;
        $inputs['config_policy'] = $inputs['config_policy'] ?? $this->model->config_policy;
        $inputs['refund_soon_cancel_booking'] = $inputs['refund_soon_cancel_booking'] ?? $this->model->refund_soon_cancel_booking;
    }

    protected function setModelFields($inputs): void
    {
        Common::assignField($this->model, 'name', $inputs);
        Common::assignField($this->model, 'app_name', $inputs);
        Common::assignField($this->model, 'email', $inputs);
        Common::assignField($this->model, 'phone', $inputs);
        Common::assignField($this->model, 'logo', $inputs);
        Common::assignField($this->model, 'address', $inputs);
        Common::assignField($this->model, 'website', $inputs);
        Common::assignField($this->model, 'date_format', $inputs);
        Common::assignField($this->model, 'time_format', $inputs);
        Common::assignField($this->model, 'timezone', $inputs);
        Common::assignField($this->model, 'locale', $inputs);
        Common::assignField($this->model, 'status', $inputs);
        Common::assignField($this->model, 'allow_signup', $inputs);
        Common::assignField($this->model, 'config_policy', $inputs);
        Common::assignField($this->model, 'refund_soon_cancel_booking', $inputs);
    }

    public function get($id)
    {
        return $this->model->hasPermission()->findOrFail($id);
    }

    /**
     * @throws ApiException
     */
    public function update(Client $client, array $inputs, array $options = []): Client
    {
        $this->setModel($client);
        if ($options['isPrefix']) {
            $inputs = Common::mappingRemovePrefix($inputs, self::FORM_PREFIX);
        }
        $this->formatInputData($inputs);
        $this->setModelFields($inputs);
        $this->model->save();

        return $client;
    }

    public function getListClientForGuest($id = null)
    {
        return $this->model
            ->select('id', 'name')
            ->when(empty($id), fn ($query) => $query->where('status', ClientStatus::PUBLIC))
            ->when($id, fn ($query) => $query->where('id', $id))
            ->get();
    }
    public function initDefaultData(): static
    {
        $this->model->date_format = 'd-m-Y';
        $this->model->time_format = 'H:i';
        $this->model->timezone = 'Asia/Ho_Chi_Minh';
        $this->model->locale = 'vi';
        $this->model->status = ClientStatus::PUBLIC;
        $this->model->allow_signup = CommonConstant::DATABASE_YES;

        return $this;
    }
    public function getClientByLicenseId($licenseId)
    {
        return $this->model
            ->leftJoin('licenses', 'licenses.client_id', '=', 'clients.id')
            ->leftJoin('lockers', 'lockers.id', '=', 'licenses.locker_id')
            ->where('licenses.id', $licenseId)
            ->select('clients.name', 'clients.phone', 'clients.logo',
                'lockers.code', 'lockers.status')
            ->first();
    }
}
