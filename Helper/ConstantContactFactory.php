<?php

namespace SpringImport\ConstantContact\Helper;

use Magento\Framework\App\Helper\Context;
use SpringImport\ConstantContact\Services\ConstantContact;
use SpringImport\ConstantContact\Helper\Data;
use Magento\Framework\Exception\InputException;

class ConstantContactFactory extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * ConstantContact list of classes
     *
     * @var array
     */
    private $constantContactByData = [];

    /**
     * ConstantContactFactory constructor.
     * @param Context $context
     * @param Data $helperData
     */
    public function __construct(Context $context, Data $helperData)
    {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Return new instance of ConstantContact with apply API data
     *
     * @param array $data
     * @return ConstantContact
     * @throws InputException
     */
    public function create(array $data = [])
    {
        $data['key'] = isset($data['key']) ? $data['key'] : $this->helperData->getKey();
        $data['accessToken'] = isset($data['accessToken']) ? $data['accessToken'] : $this->helperData->getAccessToken();

        if (!$data['key'] or !$data['accessToken']) {
            throw new InputException(__('ConstantContact API data is empty!'));
        }

        // save class to list
        $dataKey = $data['key'] . $data['accessToken'];
        if (!isset($this->constantContactByData[$dataKey])) {
            $this->constantContactByData[$dataKey] = new ConstantContact($data['key'], $data['accessToken']);
        }

        return $this->constantContactByData[$dataKey];
    }
}
