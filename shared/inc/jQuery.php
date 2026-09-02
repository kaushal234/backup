<?php

// Class specifically for eParts
class jQuery_Section
{
    public $id;
    public $html;
    public $options;

    private static $_itsParent;

    public function __construct($id, jQuery &$parentObject = NULL)
    {
        $this->id = $id;
        $this->html = '';
        $this->options = array($id);
        if (isset($parentObject))
        {
            self::$_itsParent =& $parentObject;
        }
    }

    public function addOptions(Array $options)
    {
        $inherited_options = array();
        if (self::$_itsParent)
        {
            $inherited_options = self::$_itsParent->getOptions();
        }
        $this->options = array_merge($inherited_options, $this->options, $options);
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setText($text)
    {
        $this->html = htmlentities($text);
        return $this;
    }

    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }

    public function getRequest()
    {
        return $_REQUEST[$this->id];
    }

    public function getHtml()
    {
        return $this->html;
    }

    public function __toString()
    {
        return $this->getHtml();
    }
}

class jQuery extends jQuery_Section
{
	protected $_sections;

	public function __construct($run)
	{
		$this->_sections = array();
		parent::__construct($run);
	}

	public function addSection($id)
	{
		$this->_sections[$id] = new jQuery_Section($id, $this);
		return $this->_sections[$id];
	}

	public function get($id)
	{
		return $this->getSection($id);
	}

	public function getSection($id)
	{
		if (isset($this->_sections[$id]))
		{
			return $this->_sections[$id];
		}

		return NULL;
	}

	public function getSections()
	{
		return array_keys($this->_sections);

	}

	public function getRequest($id = NULL)
	{
		if (isset($id))
		{
			return $this->_sections[$id]->getRequest();
		}
		return parent::getRequest();
	}

	public function encode(Array $options)
	{
		$array = array(
			'run' => $this->id,
			'options' => $options,
		);
        array_walk_recursive($array, function(&$item) {
            $item =  mb_convert_encoding($item, 'UTF-8', mb_list_encodings());
        });
		$encoded = json_encode($array, JSON_FORCE_OBJECT);

		$encoded = base64_encode($encoded);
		return $encoded;
	}

	public function getHtmlAttribute(jQuery_Section $object = NULL)
	{
		if (isset($object))
		{
			$options = $object->getOptions();
		}
		else
		{
			$options = $this->getOptions();
		}

		return 'data-jquery="' . htmlentities($this->encode($options)) . '"';
	}

	public function __toString()
	{
		return $this->getHtmlAttribute();
	}

	public function __get($name)
	{
		if (isset($this->_sections[$name]))
		{
			return $this->getHtmlAttribute($this->_sections[$name]);
		}
	}

	public function __isset($name)
	{
		return isset($this->_sections[$name]);
	}

	public function __unset($name)
	{
		unset($this->_sections[$name]);
	}
}
