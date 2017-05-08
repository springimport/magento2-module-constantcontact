<?php

namespace SpringImport\ConstantContact\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_PATH_GENERAL_API_CREDENTIALS_KEY = 'constantcontact/api_credentials/key';
    const XML_PATH_GENERAL_API_CREDENTIALS_ACCESS_TOKEN = 'constantcontact/api_credentials/access_token';

    /**
     * Get api key
     *
     * @return string
     */
    public function getKey()
    {
        return trim($this->scopeConfig->getValue(self::XML_PATH_GENERAL_API_CREDENTIALS_KEY));
    }

    /**
     * Get api access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return trim($this->scopeConfig->getValue(self::XML_PATH_GENERAL_API_CREDENTIALS_ACCESS_TOKEN));
    }
}
