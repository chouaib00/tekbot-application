<?php
namespace Model;
// Updated 11/5/2019
/**
 * Data class representing a user access level enumeration.
 */
class UserAccessLevel {
    const STUDENT = 1;
    const EMPLOYEE = 2;

    /** @var integer */
    private $id;
    
    /** @var string */
    private $name;

    /**
     * Creates a new instance of a UserAccessLevel.
     *
     * @param integer $id the ID of the UserType. This should come directly from the database.
     * @param string $name the name of the type
     */
    public function __construct($id = null, $name = null) {
        if ($id == null && $name == null) {
            $this->setId(self::STUDENT);
            $this->setName('Student');
        } else {
            $this->setId($id);
            $this->setName($name);
        }
    }

    /**
     * Get the value of name
     */ 
    public function getName() {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of id
     */ 
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id) {
        $this->id = $id;

        return $this;
    }
}

?>