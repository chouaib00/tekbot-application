<?php
namespace Api;

use Model\KitEnrollment;
use Model\KitEnrollmentStatus;
use DataAccess\QueryUtils;



/**
 * Defines the logic for how to handle AJAX requests made to modify equipment information.
 */
class KitEnrollmentActionHandler extends ActionHandler {

    /** @var \DataAccess\equipmentDao */
    private $kitEnrollmentDao;
    /** @var \Email\ProjectMailer */
    private $mailer;
    /** @var \Util\ConfigManager */
    private $config;
    
    /**
     * Constructs a new instance of the action handler for requests on equipment resources.
     * @param \DataAccess\kitEnrollmentDao $usersDao the data access object for users
     * @param \Email\ProjectMailer $mailer the mailer used to send equipment related emails
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($kitEnrollmentDao, $mailer, $config, $logger) {
        parent::__construct($logger);
        $this->kitEnrollmentDao = $kitEnrollmentDao;
        $this->mailer = $mailer;
        $this->config = $config;
    }

    /**
     * Creates a new equipment entry in the database.
     *
     * @return void
     */
    public function handleCreateEnrollments() {
        // Ensure all the requred parameters are present
        $this->requireParam('htmlData');
        $this->requireParam('termData');
        $body = $this->requestBody;
        
        $termInfo = $body['termData'];
        // This stips the html tags from the data recieved
        $test = str_replace("<td>", "~", $body['htmlData']);
        $test = str_replace("</td>", "", $test);
        $test = str_replace("<tr>", "", $test);
        $test = str_replace("</tr>", "", $test);
        $kitArray = explode("~", $test);
        // Gets rid of first empty element
        array_splice($kitArray, 0, 1);
        $errors = "";
        $count = 1;

        foreach ($kitArray as $k){
            // Building our object
            // Format is:
            // OSU ID, ONID, NAME, COURSE CODE, TERM NUMBER
            if ($count == 1){
                // Contains date created, status and unique ID
                $kit = new KitEnrollment();
                $kit->setOsuID($k);
                $kit->setTermID($termInfo);
                $kit->setKitStatusID(KitEnrollmentStatus::READY);
            } else if ($count == 2){
                $kit->setOnid($k);
            } else if ($count == 3){
                $kit->setFirstMiddleLastName($k);
            } else if ($count == 4) {
                $kit->setCourseCode($k);
                $ok = $this->kitEnrollmentDao->addNewKitEnrollment($kit);
                if (!$ok){
                    $errors .= "
                    Was unable to add student with onid: ". $kit->getOnid() . "";
                }
                $count = 0;
            }

            $count++;
        }

        if ($errors === ""){
            $this->respond(new Response(
                Response::CREATED, 
                'Successfully added all kits'
            ));
        } else {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, $errors));
        }
        /*
        $equipment = new Equipment();
        $equipment->setEquipmentName($body['title']);
        $equipment->setDateCreated(new \DateTime());

        $ok = $this->equipmentDao->addNewEquipment($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new equipment'));
        }
        */
        $this->respond(new Response(
            Response::CREATED, 
            $kitArray
        ));
    }

    /**
     * Creates a new equipment entry in the database.
     *
     * @return void
     */
    public function handleParseInput() {
        // Ensure all the requred parameters are present
        $this->requireParam('jsonData');
        $count = 1;
        $body = $this->requestBody;
        /*
        if ((substr($body['jsonData'],0,1) == "\n") || (substr($body['jsonData'],-1) == "\n")) {
            ltrim($body['jsonData'], "\n");
            rtrim($body['jsonData'], "\n");

        }*/
        
        //$table = "<table><tr><th>ID Number</th><th>Name</th><th>Course</th><th>Email</th></tr>";
        $table = "";
        foreach(preg_split("/[\t\n]/", $body['jsonData']) as $line){
            if ($count == 1){
                $table .= "<tr>";
            }
            $table .= "<td>";
            $table .= $line;
            $table .= "</td>";
            // Need to change count to number of elements in the table (If changing this also change handleCreateEnrollments)
            if ($count == 4){
                $table .= "</tr>";
                $count = 0;
            }
            $count++;
        }
        


        //$table .="</table>";

        $this->respond(new Response(
            Response::OK, 
            $table
        ));
    }


    /**
     * Updates a kit enrollment, marking it as handed out or not
     *
     * @return void
     */
    public function handleHandoutKitEnrollment() {
        $this->requireParam('kid');
        $this->requireParam('status');

        $body = $this->requestBody;
        $id = $body['kid'];
        $statusID = $body['status'];

        $kit = $this->kitEnrollmentDao->getKitEnrollment($id);
        if (empty($kit)){
            $this->respond(new Response(Response::NOT_FOUND, 'Unable to obtain kit from ID'));
        }
        if ($statusID == KitEnrollmentStatus::READY){
            $actionText = "returned";
        } else if ($statusID == KitEnrollmentStatus::PICKED_UP){
            $actionText = "handed out";
        }

        $kit->setKitStatusID($statusID);
        $kit->setDateUpdated(new \Datetime);

        $ok = $this->kitEnrollmentDao->updateKitEnrollment($kit);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update kit enrollment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully '.$actionText.' kit'
        ));
    }

    /**
     * Sets is_public to true for equipment
     *
     * @return void
     */
    public function handleMakePublicEquipment() {
        
    }

    /**
     * Sets is_public to false for equipment
     *
     * @return void
     */
    public function handleMakeHiddenEquipment() {
        $id = $this->getFromBody('equipmentID');

        $equipment = $this->equipmentDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setIsPublic(FALSE);
        $equipment->setDateUpdated(new \Datetime);

        $ok = $this->equipmentDao->updateEquipmentVisiblity($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to hide equipment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully hid equipment'
        ));
    }

    /**
     * Sets is_public to true for equipment
     *
     * @return void
     */
    public function handleShowEquipment() {
        $id = $this->getFromBody('equipmentID');

        $equipment = $this->equipmentDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setIsPublic(TRUE);
        $equipment->setDateUpdated(new \Datetime);

        $ok = $this->equipmentDao->updateEquipmentVisiblity($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to hide equipment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully made equipment public'
        ));
    }

    /**
     * Handles updating the default image for a equipment in the database.
     *
     * @return void
     */
    public function handleDefaultImageSelected() {
        $imageId = $this->getFromBody('imageID');

        $image = $this->equipmentDao->getEquipmentImage($imageId);
        if (empty($image)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain image from ID'));
        }

        $image->setIsDefault(true);

        $ok = $this->equipmentDao->updateDefaultEquipmentImage($image);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update equipment image'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully updated default capstone image',
            array('name' => $image->getImageName())
        ));
    }

 
    /**
     * Handles archiving an equipment in the database.
     *
     * @return void
     */
    public function handleArchiveEquipment() {
        $id = $this->getFromBody('equipmentID');

        $equipment = $this->equipmentDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setIsArchived(true);

        $ok = $this->equipmentDao->updateEquipmentVisiblity($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to archive equipment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully removed equipment'
        ));
    }

    

    /**
     * Handles the HTTP request on the API resource. 
     * 
     * This effectively will invoke the correct action based on the `action` parameter value in the request body. If
     * the `action` parameter is not in the body, the request will be rejected. The assumption is that the request
     * has already been authorized before this function is called.
     *
     * @return void
     */
    public function handleRequest() {
        // Make sure the action parameter exists
        $action = $this->getFromBody('action');

        // Call the correct handler based on the action
        switch ($action) {

            case 'showParseInput':
                $this->handleParseInput();

            case 'uploadKitEnrollments':
                $this->handleCreateEnrollments();

            case 'updateHandoutKitEnrollments':
                $this->handleHandoutKitEnrollment();

            case 'makeEquipmentHidden':
                $this->handleMakeHiddenEquipment();

            case 'makeEquipmentShown':
                $this->handleShowEquipment();

            case 'makeEquipmentArchive':
                $this->handleArchiveEquipment();

            case 'makeEquipmentPublic':
                $this->handleMakePublicEquipment();

            case 'defaultImageSelected':
                $this->handleDefaultImageSelected();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on equipment resource'));
        }
    }

    private function getAbsoluteLinkTo($path) {
        return $this->config->getBaseUrl() . $path;
    }
}
