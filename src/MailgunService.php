<?php
/**
 * Created by PhpStorm.
 * User: dougniccum
 * Date: 9/4/18
 * Time: 9:40 PM
 */

namespace Dniccum\MailgunDomainVerification;

use Mailgun\Mailgun;

class MailgunService
{
    /**
     * @var Mailgun $mailgun
     */
    public $mailgun;

    /**
     * @var string $model
     */
    public $model;

    /**
     * @var string $columnAttribute
     */
    public $columnAttribute;

    /**
     * MailgunService constructor.
     *
     * @param string $model
     * @param string $columnAttribute
     */
    public function __construct(string $model, string $columnAttribute='email_address_domain')
    {
        $this->mailgun = new Mailgun(config('services.mailgun.secret'), new \Http\Adapter\Guzzle6\Client());
        $this->model = $model;
        $this->columnAttribute = $columnAttribute;
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Query\Builder|mixed
     */
    private function getDomain($id)
    {
        $modelResult = \DB::table($this->model)->find($id);

        return $modelResult;
    }

    /**
     * Gets the status of the domain
     * @param int $resultId
     * @return string
     */
    public function getDomainStatus(int $resultId)
    {
        $modelResult = $this->getDomain($resultId);
        $attribute = $this->columnAttribute;

        if (empty($modelResult->$attribute)) {
            return false;
        }

        $result = $this->mailgun->get("domains/".$modelResult->$attribute, [
            'force_dkim_authority' => true
        ]);

        header('Content-Type: application/json');

        return $result;
    }

    /**
     * Re-verify the domain status
     * @param int $resultId
     * @return string
     */
    public function verifyDomainStatus(int $resultId)
    {
        $modelResult = $this->getDomain($resultId);
        $attribute = $this->columnAttribute;

        if (empty($modelResult->$attribute)) {
            return false;
        }

        $result = $this->mailgun->put("domains/".$modelResult->$attribute."/verify", [
            'force_dkim_authority' => true
        ]);

        header('Content-Type: application/json');

        return $result;
    }

    /**
     * Adds domain to the Mailgun account via the domain API
     * @param int $resultId
     * @return string
     */
    public function addDomain(int $resultId)
    {
        $modelResult = $this->getDomain($resultId);
        $attribute = $this->columnAttribute;

        if (empty($modelResult->$attribute)) {
            return false;
        }

        $result = $this->mailgun->post("domains", [
            'name' => $modelResult->$attribute,
            'force_dkim_authority' => true
        ]);

        header('Content-Type: application/json');

        return $result;
    }
}
