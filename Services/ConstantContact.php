<?php

namespace SpringImport\ConstantContact\Services;

use Ctct\Services\AccountService;
use Ctct\Services\ContactService;
use Ctct\Services\LibraryService;
use Ctct\Services\ListService;
use Ctct\Services\EmailMarketingService;
use Ctct\Services\CampaignScheduleService;
use Ctct\Services\CampaignTrackingService;
use Ctct\Services\ContactTrackingService;
use Ctct\Services\ActivityService;
use Ctct\Components\Account\AccountInfo;
use Ctct\Components\Activities\Activity;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\EmailMarketing\Campaign;
use Ctct\Components\EmailMarketing\Schedule;
use Ctct\Components\EmailMarketing\TestSend;
use Ctct\Components\ResultSet;
use Ctct\Components\Tracking\TrackingSummary;
use Ctct\Components\Activities\AddContacts;
use Ctct\Components\Activities\ExportContacts;
use Ctct\Exceptions\IllegalArgumentException;
use Ctct\Util\Config;

/**
 * Exposes all implemented Constant Contact API functionality
 */
class ConstantContact
{
    /**
     * Constant Contact API Key
     * @var string
     */
    private $apiKey;

    /**
     * Constant Contact API Access Token
     * @var string
     */
    private $accessToken;

    /**
     * Handles handling interaction with contact management
     * @var ContactService
     */
    protected $contactService;

    /**
     * Handles interaction with email marketing
     * @var EmailMarketingService
     */
    protected $emailMarketingService;

    /**
     * Handles interaction with contact list management
     * @var ListService
     */
    protected $listService;

    /**
     * ActivityService for handling interaction with bulk activities
     * @var ActivityService
     */
    protected $activityService;

    /**
     * Handles interaction with email marketing tracking
     * @var CampaignTrackingService
     */
    protected $campaignTrackingService;

    /**
     * Handles interaction with contact tracking
     * @var ContactTrackingService
     */
    protected $contactTrackingService;

    /**
     * Handles interaction with email marketing campaign scheduling
     * @var CampaignScheduleService
     */
    protected $campaignScheduleService;

    /**
     * Handles interaction with account management
     * @var AccountService
     */
    protected $accountService;

    /**
     * Handles interaction with Library management
     * @var LibraryService
     */
    protected $libraryService;

    /**
     * Class constructor
     * Registers the API key with the ConstantContactFactory class that will be used for all API calls.
     * @param string $apiKey - Constant Contact API Key
     * @param string $accessToken - Constant Contact API Access Token
     */
    public function __construct($apiKey, $accessToken)
    {
        $this->apiKey = $apiKey;
        $this->accessToken = $accessToken;
        $this->contactService = new ContactService($apiKey);
        $this->emailMarketingService = new EmailMarketingService($apiKey);
        $this->activityService = new ActivityService($apiKey);
        $this->campaignTrackingService = new CampaignTrackingService($apiKey);
        $this->contactTrackingService = new ContactTrackingService($apiKey);
        $this->campaignScheduleService = new CampaignScheduleService($apiKey);
        $this->listService = new ListService($apiKey);
        $this->accountService = new AccountService($apiKey);
        $this->libraryService = new LibraryService($apiKey);
    }

    /**
     * Get a set of campaigns
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      modified_since - ISO-8601 formatted timestamp.
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     *      email - the contact by email address to retrieve information for.
     *      status - a contact status to filter results by. Must be one of ACTIVE, OPTOUT, REMOVED, UNCONFIRMED.
     * @return ResultSet containing a results array of {@link Ctct\Components\Contacts\Contact}
     */
    public function getContacts(array $params = [])
    {
        return $this->contactService->getContacts($this->accessToken, $params);
    }

    /**
     * Get an individual contact
     * @param int $contactId - Id of the contact to retrieve
     * @return Contact
     */
    public function getContact($contactId)
    {
        return $this->contactService->getContact($this->accessToken, $contactId);
    }

    /**
     * Get contacts with a specified email address
     * @param string $email - contact email address to search for
     * @return ResultSet
     */
    public function getContactByEmail($email)
    {
        return $this->contactService->getContacts($this->accessToken, ['email' => $email]);
    }

    /**
     * Add a new contact to an account
     * @param Contact $contact - Contact to add
     * @param boolean $actionByVisitor - is the action being taken by the visitor
     * @return Contact
     */
    public function addContact(Contact $contact, $actionByVisitor = false)
    {
        $params = [];
        if ($actionByVisitor == true) {
            $params['action_by'] = "ACTION_BY_VISITOR";
        }
        return $this->contactService->addContact($this->accessToken, $contact, $params);
    }

    /**
     * Sets an individual contact to 'REMOVED' status
     * @param mixed $contact - Either a Contact id or the Contact itself
     * @throws IllegalArgumentException - if an int or Contact object is not provided
     * @return boolean
     */
    public function deleteContact($contact)
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactService->deleteContact($this->accessToken, $contactId);
    }

    /**
     * Delete a contact from all contact lists
     * @param mixed $contact - Contact id or the Contact object itself
     * @throws IllegalArgumentException - if an int or Contact object is not provided
     * @return boolean
     */
    public function deleteContactFromLists($contact)
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactService->deleteContactFromLists($this->accessToken, $contactId);
    }

    /**
     * Delete a contact from all contact lists
     * @param mixed $contact - Contact id or a Contact object
     * @param mixed $list - ContactList id or a ContactList object
     * @throws IllegalArgumentException - if an int or Contact object is not provided,
     * as well as an int or ContactList object
     * @return boolean
     */
    public function deleteContactFromList($contact, $list)
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        $listId = $this->getArgumentId($list, 'ContactList');

        return $this->contactService->deleteContactFromList($this->accessToken, $contactId, $listId);
    }

    /**
     * Update an individual contact
     * @param Contact $contact - Contact to update
     * @param boolean $actionByVisitor - is the action being taken by the visitor, default is false
     * @return Contact
     */
    public function updateContact(Contact $contact, $actionByVisitor = false)
    {
        $params = [];
        if ($actionByVisitor == true) {
            $params['action_by'] = "ACTION_BY_VISITOR";
        }
        return $this->contactService->updateContact($this->accessToken, $contact, $params);
    }

    /**
     * Get lists
     * @param array $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      modified_since - ISO-8601 formatted timestamp.
     * @return array of ContactList
     */
    public function getLists(array $params = [])
    {
        return $this->listService->getLists($this->accessToken, $params);
    }

    /**
     * Get an individual list
     * @param int $listId - Id of the list to retrieve
     * @return ContactList
     */
    public function getList($listId)
    {
        return $this->listService->getList($this->accessToken, $listId);
    }

    /**
     * Add a new contact list to an account
     * @param ContactList $list - List to add
     * @return ContactList
     */
    public function addList(ContactList $list)
    {
        return $this->listService->addList($this->accessToken, $list);
    }

    /**
     * Update a contact list
     * @param ContactList $list - ContactList to update
     * @return ContactList
     */
    public function updateList(ContactList $list)
    {
        return $this->listService->updateList($this->accessToken, $list);
    }

    /**
     * Delete a contact list from an account
     * @param int $listId - Id of the list to delete
     * @return boolean
     */
    public function deleteList($listId)
    {
        return $this->listService->deleteList($this->accessToken, $listId);
    }

    /**
     * Get contact that belong to a specific list
     * @param mixed $list - Id of the list or a ContactList object
     * @param mixed $param - denotes the number of results per set, limited to 50, or a next parameter provided
     * from a previous getContactsFromList call
     * @return ResultSet
     * @throws IllegalArgumentException - if a ContactList object or id is not passed
     */
    public function getContactsFromList($list, $param = null)
    {
        $listId = $this->getArgumentId($list, 'ContactList');
        $param = $this->determineParam($param);
        return $this->listService->getContactsFromList($this->accessToken, $listId, $param);
    }

    /**
     * Get a set of campaigns
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      modified_since - ISO-8601 formatted timestamp.
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     *      email - the contact by email address to retrieve information for
     * @return ResultSet containing a results array of {@link Ctct\Components\EmailMarketing\Emaillists}
     */
    public function getEmailCampaigns(array $params = [])
    {
        return $this->emailMarketingService->getCampaigns($this->accessToken, $params);
    }

    /**
     * Get an individual campaign
     * @param int $campaignId - Valid campaign id
     * @return \Ctct\Components\EmailMarketing\Campaign
     */
    public function getEmailCampaign($campaignId)
    {
        return $this->emailMarketingService->getCampaign($this->accessToken, $campaignId);
    }

    /**
     * Delete an individual campaign
     * @param mixed $campaign - Id of a campaign or a Emaillists object itself
     * @throws IllegalArgumentException - if a Emaillists object or campaign id is not passed
     * @return boolean
     */
    public function deleteEmailCampaign($campaign)
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->emailMarketingService->deleteCampaign($this->accessToken, $campaignId);
    }

    /**
     * Create a new campaign
     * @param Campaign $campaign - Emaillists to be created
     * @return Campaign - created campaign
     */
    public function addEmailCampaign(Campaign $campaign)
    {
        return $this->emailMarketingService->addCampaign($this->accessToken, $campaign);
    }

    /**
     * Update a specific campaign
     * @param Campaign $campaign - Emaillists to be updated
     * @return Campaign - updated campaign
     */
    public function updateEmailCampaign(Campaign $campaign)
    {
        return $this->emailMarketingService->updateCampaign($this->accessToken, $campaign);
    }

    /**
     * Schedule a campaign to be sent
     * @param mixed $campaign - Emaillists to be updated
     * @param Schedule $schedule - Schedule to be associated with the provided campaign
     * @return Schedule schedule created
     */
    public function addEmailCampaignSchedule($campaign, Schedule $schedule)
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignScheduleService->addSchedule($this->accessToken, $campaignId, $schedule);
    }

    /**
     * Get an array of schedules associated with a given campaign
     * @param mixed $campaign - Emaillists id  or Emaillists object itself
     * @return array
     */
    public function getEmailCampaignSchedules($campaign)
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignScheduleService->getSchedules($this->accessToken, $campaignId);
    }

    /**
     * Get a specific schedule associated with a given campaign
     * @param mixed $campaign - Emaillists id or Emaillists object itself
     * @param mixed $schedule - Schedule id or Schedule object itself
     * @throws IllegalArgumentException
     * @return Schedule
     */
    public function getEmailCampaignSchedule($campaign, $schedule)
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        $scheduleId = null;

        if ($schedule instanceof Schedule) {
            $scheduleId = $schedule->id;
        } elseif (is_numeric($schedule)) {
            $scheduleId = $schedule;
        } else {
            throw new IllegalArgumentException(sprintf(Config::get('errors.id_or_object'), 'Schedule'));
        }

        return $this->campaignScheduleService->getSchedule($this->accessToken, $campaignId, $scheduleId);
    }

    /**
     * Update a specific schedule associated with a given campaign
     * @param mixed $campaign - Emaillists id or Emaillists object itself
     * @param Schedule $schedule - Schedule to be updated
     * @return Schedule
     */
    public function updateEmailCampaignSchedule($campaign, Schedule $schedule)
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignScheduleService->updateSchedule($this->accessToken, $campaignId, $schedule);
    }

    /**
     * Delete a specific schedule associated with a given campaign
     * @param mixed $campaign - Emaillists id or Emaillists object itself
     * @param mixed $schedule - Schedule id or Schedule object itself
     * @throws IllegalArgumentException
     * @return boolean
     */
    public function deleteEmailCampaignSchedule($campaign, $schedule)
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        $scheduleId = null;

        if ($schedule instanceof Schedule) {
            $scheduleId = $schedule->id;
        } elseif (is_numeric($schedule)) {
            $scheduleId = $schedule;
        } else {
            throw new IllegalArgumentException(sprintf(Config::get('errors.id_or_object'), 'Schedule'));
        }

        return $this->campaignScheduleService->deleteSchedule($this->accessToken, $campaignId, $scheduleId);
    }

    /**
     * Send a test send of a campaign
     * @param mixed $campaign  - Emaillists id or Emaillists object itself
     * @param TestSend $testSend - test send details
     * @return TestSend
     */
    public function sendEmailCampaignTest($campaign, TestSend $testSend)
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignScheduleService->sendTest($this->accessToken, $campaignId, $testSend);
    }

    /**
     * Get sends for a campaign
     * @param mixed $campaign - Emaillists id or Emaillists object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\SendActivity}
     */
    public function getEmailCampaignSends($campaign, array $params = [])
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignTrackingService->getSends($this->accessToken, $campaignId, $params);
    }

    /**
     * Get bounces for a campaign
     * @param mixed $campaign  - Emaillists id or Emaillists object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\BounceActivity}
     */
    public function getEmailCampaignBounces($campaign, array $params = [])
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignTrackingService->getBounces($this->accessToken, $campaignId, $params);
    }

    /**
     * Get clicks for a campaign
     * @param mixed $campaign  - Emaillists id or Emaillists object itself
     * @param array $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\ClickActivity}
     */
    public function getEmailCampaignClicks($campaign, array $params = [])
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignTrackingService->getClicks($this->accessToken, $campaignId, $params);
    }

    /**
     * Get opens for a campaign
     * @param mixed $campaign  - Emaillists id or Emaillists object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\OpenActivity}
     */
    public function getEmailCampaignOpens($campaign, array $params = [])
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignTrackingService->getOpens($this->accessToken, $campaignId, $params);
    }

    /**
     * Get forwards for a campaign
     * @param mixed $campaign  - Emaillists id or Emaillists object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\ForwardActivity}
     */
    public function getEmailCampaignForwards($campaign, array $params = [])
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignTrackingService->getForwards($this->accessToken, $campaignId, $params);
    }

    /**
     * Get unsubscribes for a campaign
     * @param mixed $campaign  - Emaillists id or Emaillists object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\UnsubscribeActivity}
     */
    public function getEmailCampaignUnsubscribes($campaign, array $params = [])
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignTrackingService->getUnsubscribes($this->accessToken, $campaignId, $params);
    }

    /**
     * Get a reporting summary for a campaign
     * @param mixed $campaign  - Emaillists id or Emaillists object itself
     * @return TrackingSummary
     */
    public function getEmailCampaignSummaryReport($campaign)
    {
        $campaignId = $this->getArgumentId($campaign, 'Emaillists');
        return $this->campaignTrackingService->getSummary($this->accessToken, $campaignId);
    }

    /**
     * Get sends for a Contact
     * @param mixed $contact  - Contact id or Contact object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\SendActivity}
     */
    public function getContactSends($contact, array $params = [])
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactTrackingService->getSends($this->accessToken, $contactId, $params);
    }

    /**
     * Get bounces for a Contact
     * @param mixed $contact  - Contact id or Contact object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\BounceActivity}
     */
    public function getContactBounces($contact, array $params = [])
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactTrackingService->getBounces($this->accessToken, $contactId, $params);
    }

    /**
     * Get clicks for a Contact
     * @param mixed $contact  - Contact id or Contact object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\ClickActivity}
     */
    public function getContactClicks($contact, array $params = [])
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactTrackingService->getClicks($this->accessToken, $contactId, $params);
    }

    /**
     * Get opens for a Contact
     * @param mixed $contact  - Contact id or Contact object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\OpenActivity}
     */
    public function getContactOpens($contact, array $params = [])
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactTrackingService->getOpens($this->accessToken, $contactId, $params);
    }

    /**
     * Get forwards for a Contact
     * @param mixed $contact  - Contact id or Contact object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\ForwardActivity}
     */
    public function getContactForwards($contact, array $params = [])
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactTrackingService->getForwards($this->accessToken, $contactId, $params);
    }

    /**
     * Get opt outs for a Contact
     * @param mixed $contact  - Contact id or Contact object itself
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 500, default = 50.
     *      created_since - Used to retrieve a list of events since the date and time specified (in ISO-8601 format).
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Tracking\UnsubscribeActivity}
     */
    public function getContactUnsubscribes($contact, array $params = [])
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactTrackingService->getUnsubscribes($this->accessToken, $contactId, $params);
    }

    /**
     * Get verified addresses for the account
     * @param string $status - Status to filter query results by
     * @return array of VerifiedEmailAddress objects
     */
    public function getVerifiedEmailAddresses($status = null)
    {
        $params = [];
        if ($status) {
            $params['status'] = $status;
        }
        return $this->accountService->getVerifiedEmailAddresses($this->accessToken, $params);
    }

    /**
     * Create new verified email addresses. This will also prompt the account to send
     * a verification email to the address.
     * @param string $emailAddress - email address to create
     * @return array - array of VerifiedEmailAddress created
     */
    public function createVerifiedEmailAddress($emailAddress)
    {
        return $this->accountService->createVerifiedEmailAddress($this->accessToken, $emailAddress);
    }

    /**
     * Get details for account associated with an access token
     * @return AccountInfo
     */
    public function getAccountInfo()
    {
        return $this->accountService->getAccountInfo($this->accessToken);
    }

    /**
     * Update information of the account.
     * @param AccountInfo $accountInfo - Updated AccountInfo
     * @return AccountInfo
     */
    public function updateAccountInfo($accountInfo)
    {
        return $this->accountService->updateAccountInfo($this->accessToken, $accountInfo);
    }

    /**
     * @param string $fileId - File Id
     * @return File
     */
    public function getLibraryFile($fileId)
    {
        return $this->libraryService->getLibraryFile($this->accessToken, $fileId);
    }

    /**
     * @param string $folderId - Optionally search for files in a specified folder
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 1000, default = 50.
     *      sort_by - Specifies how the list of files is sorted; valid sort options are:
     *          CREATED_DATE, CREATED_DATE_DESC, MODIFIED_DATE, MODIFIED_DATE_DESC, NAME, NAME_DESC, SIZE, SIZE_DESC DIMENSION, DIMENSION_DESC
     *      source - Specifies to retrieve files from a particular source:
     *          ALL, MyComputer, Facebook, Instagram, Shutterstock, Mobile
     *      next - the next link returned from a previous paginated call. May only be used by itself.
     * @return ResultSet - Containing a results array of {@link Ctct\Components\Library\File}
     */
    public function getLibraryFiles($folderId = null, array $params = [])
    {
        if ($folderId) {
            return $this->libraryService->getLibraryFilesByFolder($this->accessToken, $folderId, $params);
        }
        return $this->libraryService->getLibraryFiles($this->accessToken, $params);
    }

    /**
     * @param mixed $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      limit - Specifies the number of results displayed per page of output, from 1 - 1000, default = 50.
     *      sort_by - Specifies how the list of files is sorted; valid sort options are:
     *          CREATED_DATE, CREATED_DATE_DESC, MODIFIED_DATE, MODIFIED_DATE_DESC, NAME, NAME_DESC
     * @return ResultSet
     */
    public function getLibraryFolders(array $params = [])
    {
        return $this->libraryService->getLibraryFolders($this->accessToken, $params);
    }

    /**
     * Get a reporting summary for a Contact
     * @param mixed $contact  - Contact id or Contact object itself
     * @return TrackingSummary
     */
    public function getContactSummaryReport($contact)
    {
        $contactId = $this->getArgumentId($contact, 'Contact');
        return $this->contactTrackingService->getSummary($this->accessToken, $contactId);
    }

    /**
     * Get an array of activities
     * @param array $params - associative array of query parameters and values to append to the request.
     *      Allowed parameters include:
     *      status - Status of the activity, must be one of UNCONFIRMED, PENDING, QUEUED, RUNNING, COMPLETE, ERROR
     *      type - Type of activity, must be one of ADD_CONTACTS, REMOVE_CONTACTS_FROM_LISTS, CLEAR_CONTACTS_FROM_LISTS,
     *             EXPORT_CONTACTS
     * @return Activity
     */
    public function getActivities(array $params = [])
    {
        return $this->activityService->getActivities($this->accessToken, $params);
    }

    /**
     * Get a single activity by id
     * @param string $activityId - Activity id
     * @return Activity
     */
    public function getActivity($activityId)
    {
        return $this->activityService->getActivity($this->accessToken, $activityId);
    }

    /**
     * Add an AddContacts Activity to add contacts in bulk
     * @param AddContacts $addContactsActivity - Add Contacts Activity
     * @return Activity
     */
    public function addCreateContactsActivity(AddContacts $addContactsActivity)
    {
        return $this->activityService->createAddContactsActivity($this->accessToken, $addContactsActivity);
    }

    /**
     * Create an Add Contacts Activity from a file. Valid file types are txt, csv, xls, xlsx
     * @param string $fileName - The name of the file (ie: contacts.csv)
     * @param string $contents - The contents of the file
     * @param string $lists - Comma separated list of ContactList id's to add the contacts to
     * @return Activity
     */
    public function addCreateContactsActivityFromFile($fileName, $contents, $lists)
    {
        return $this->activityService->createAddContactsActivityFromFile($this->accessToken, $fileName, $contents, $lists);
    }

    /**
     * Add an ClearLists Activity to remove all contacts from the provided lists
     * @param array $lists - Array of list id's to be cleared
     * @return Activity
     */
    public function addClearListsActivity(array $lists)
    {
        return $this->activityService->addClearListsActivity($this->accessToken, $lists);
    }

    /**
     * Add a Remove Contacts From Lists Activity
     * @param array $emailAddresses - email addresses to be removed
     * @param array $lists - lists to remove the provided email addresses from
     * @return Activity
     */
    public function addRemoveContactsFromListsActivity(array $emailAddresses, array $lists)
    {
        return $this->activityService->addRemoveContactsFromListsActivity($this->accessToken, $emailAddresses, $lists);
    }

    /**
     * Add a Remove Contacts From Lists Activity from a file. Valid file types are txt, csv, xls, xlsx
     * @param string $fileName - The name of the file (ie: contacts.csv)
     * @param string $contents - The contents of the file
     * @param string $lists - Comma separated list of ContactList id' to add the contacts too
     * @return Activity
     */
    public function addRemoveContactsFromListsActivityFromFile($fileName, $contents, $lists)
    {
        return $this->activityService->addRemoveContactsFromListsActivityFromFile(
            $this->accessToken,
            $fileName,
            $contents,
            $lists
        );
    }

    /**
     * Create an Export Contacts Activity
     * @param ExportContacts $exportContacts
     * @return Activity
     */
    public function addExportContactsActivity(ExportContacts $exportContacts)
    {
        return $this->activityService->addExportContactsActivity($this->accessToken, $exportContacts);
    }

    /**
     * Get the id of object, or attempt to convert the argument to an int
     * @param mixed $item - object or a numeric value
     * @param string $className - class name to test the given object against
     * @throws IllegalArgumentException - if the item is not an instance of the class name given, or cannot be
     * converted to a numeric value
     * @return int
     */
    private function getArgumentId($item, $className)
    {
        $id = null;

        if (is_numeric($item)) {
            $id = $item;
        } elseif (join('', array_slice(explode('\\', get_class($item)), -1)) == $className) {
            $id = $item->id;
        } else {
            throw new IllegalArgumentException(sprintf(Config::get('errors.id_or_object'), $className));
        }

        return $id;
    }

    /**
     * Builds an array of query parameters to be added to the request
     * @param string $param
     * @return array
     */
    private function determineParam($param)
    {
        $params = [];
        if (substr($param, 0, 1) === '?') {
            $param = substr($param, 1);
            parse_str($param, $params);
        } else {
            $params['limit'] = $param;
        }
        return $params;
    }
}
