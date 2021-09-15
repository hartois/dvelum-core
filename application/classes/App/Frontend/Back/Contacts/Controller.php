<?php
declare(strict_types=1);

namespace App\Frontend\Back\Contacts;

use App\Exception\BadRequest;
use App\Frontend\Back;
use Laminas\Db\Adapter\Adapter;

class Controller extends Back\Controller {
    public function getAction(){
        $uuid = $this->jsonRequest->bodyParam('uuid','string',null);
        $sql = 'select * from front.get_contact($1)';
        $data = $this->adapter->query($sql,[$uuid])->toArray();
        if(!empty($data))
            $data = $data[0];
        $this->response->success($data);
    }

    public function determinecallcontactsAction(){
        $uuid = $this->jsonRequest->bodyParam('uuid','string',null);
        $sql = 'select * from front.determine_call_contacts($1)';
        $data = $this->adapter->query($sql,[$uuid])->toArray();
        if(empty($data)) {
            $this->response->success(['contact' => null]);
            return;
        }
        $data = $data[0];
        $data['phonenums'] = json_decode($data['phonenums'],true);

        $this->response->success(['contact' => $data]);
    }

    public function savecontactAction() {
        $inputData = [
            'id_uuid' => $this->jsonRequest->bodyParam('id_uuid','string',null),
            'fullname' => $this->jsonRequest->bodyParam('fullname','string',null),
            'phonenums' => $this->jsonRequest->bodyParam('phonenums','array',null)
        ];
        if(empty($inputData['id_uuid']))
            $inputData['id_uuid'] = null;

        $sql = 'select * from front.save_contact($1) as data';
        $data = $this->adapter->query($sql,[json_encode($inputData)])->toArray();

        $this->response->success(['contact' => json_decode($data[0]['data'],true)]);
    }
}
