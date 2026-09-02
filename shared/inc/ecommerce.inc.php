<?php
/**
 *
 * @desc All classes related to shopping carts are kept in this file
 * @access public
 * @author Graham K.L. Fong <graham.fong@tld-america.com>
 * @copyright TLD
 * @package ECommerce
 */

include_once 'erp.inc.php';

/**
 * @package ECommerce
 */
class tldCart
{
    public $itsItems = [];    //array to hold items
    public $itsERP; //erp company number
    public $itsCustomer; //vendor id number in erp system
    public $itsHeader;
    public $isPriced = false;

    /**
     * Get array representation of car
     * array("header"=>array("customer"=>"","carrier"=>"","t_odat"=>""),
     *        "lines"=>array(array("item"=>array(), "qty"=>x, "back"=>xx))
     *        )
     *
     * @return array
     */
    public function toArray()
    {
        $result['header'] = $this->itsHeader;
        $result['lines'] = $this->itsItems;

        return $result;
    }

    /**
     * item is object, qty is numeric, back numeric
     *
     */
    public function add($key, $item, $qty = 1, $back = 0)
    {
        if (empty($qty)) {
            $qty = 1;
        }
        if ($this->itsItems[$key]) {
            $this->itsItems[$key]['qty'] += $qty;
            $this->itsItems[$key]['back'] += $back;
        } else {
            $this->itsItems[$key] = [
                'item' => $item,    //mixed
                'qty' => $qty,    //int
                'back' => $back    //int
            ];
        }
        $this->isPriced = false;
    }

    public function isEmpty()
    {
        return !count($this->itsItems);
    }

    public function emptyAll()
    {
        if ($this->isEmpty()) {
            return;
        }
        foreach ($this->itsItems as $key => $item) {
            $this->remove($key);
        }
        $this->isPriced = false;
    }

    public function remove($pn)
    {
        unset($this->itsItems[$pn]);
        $this->isPriced = false;
    }

    /**
     * Update line in cart with new qtys, if qty and back are set to zero then the item
     * will be deleted from cart
     *
     * @param string $pn part number of item to update
     * @param integer $qty Quantity to set
     * @param integer $back Back order quantity to set
     */
    public function update($pn, $qty = 0, $back = 0)
    {
        if (empty($pn)) {
            return;
        }
        if ($qty == 0 && $back == 0) {
            $this->remove($pn);
        } else {
            $this->itsItems[$pn]["qty"] = $qty;
            $this->itsItems[$pn]["back"] = $back;
        }
        $this->isPriced = false;
    }

    public function getItem($pn)
    {
        return $this->itsItems[$pn];
    }

    public function getHeader()
    {
        return;
    }

    public function getItems()
    {
        return $this->itsItems;
    }

    public function setCustomer($id)
    {
        $this->itsHeader["customer"] = $id;
    }

    public function getCustomer()
    {
        return $this->itsHeader["customer"];
    }

    public function setCarrier($id)
    {
        $this->itsHeader["carrier"] = $id;
    }

    public function getCarrier()
    {
        return $this->itsHeader["carrier"];
    }

    public function setDeliverDate($id)
    {
        $this->itsHeader["t_odat"] = $id;
    }

    public function getDeliverDate()
    {
        return $this->itsHeader["t_odat"];
    }

    public function setDeliverAddress($a)
    {
        $this->itsHeader["deliverAddress"] = $a;
    }

    public function getDeliverAddress()
    {
        return $this->itsHeader["deliverAddress"];
    }

    public function setCustORNO($orno)
    {
        $this->itsHeader["custpo"] = $orno;
    }

    public function getCustORNO()
    {
        return $this->itsHeader["custpo"];
    }

    public function setCDEL($cdel)
    {
        $this->itsHeader["t_cdel"] = $cdel;
    }

    public function getCDEL()
    {
        return $this->itsHeader["t_cdel"];
    }

    public function setCCOR($ccor)
    {
        $this->itsHeader["t_ccor"] = $ccor;
    }

    public function getCCOR()
    {
        return $this->itsHeader["t_ccor"];
    }

    public function setPostalAddress($a)
    {
        $this->itsHeader["postalAddress"] = $a;
    }

    public function getPostalAddress()
    {
        return $this->itsHeader["postalAddress"];
    }
}

class tldEpartsCart extends tldCart
{
    public $ePartsUser;

    public function __construct(ePartsUser $user)
    {
        $this->ePartsUser = $user;

    }

    public function priceCart($price_array)
    {
        $total = 0;

        $commission = $this->ePartsUser->getEpartsCommission();
        $ratio = (100 - $commission) / 100;

        foreach ($price_array['lines'] as $line) {
            $priceWithCommission = $line['price'] / $ratio;
            $unitPrice = round($priceWithCommission - (((float)$line['discount'] * $priceWithCommission) / 100), 2);
            $this->itsItems[$line['t_item']]['price'] = $priceWithCommission;
            $this->itsItems[$line['t_item']]['qty'] = $line['qty'];
            $this->itsItems[$line['t_item']]['discount'] = $line['discount'];
            $this->itsItems[$line['t_item']]['net_amta'] = $line['net_amta'];
            $this->itsItems[$line['t_item']]['priced_unit'] = $unitPrice;
            $this->itsItems[$line['t_item']]['priced_total'] = $unitPrice * $line['qty'];
            $total += $this->itsItems[$line['t_item']]['priced_total'];
        }

        $this->itsHeader['total'] = $total;
        $this->isPriced = true;
    }

    public function addCart($pn, $qty = 1)
    {
        if (empty($pn)) {
            return 'Missing Part Number';
        }
        if ($qty <= 0) {
            return 'Please select quantity, unable to add to cart';
        }
        $itm = $this->ePartsUser->getPartDetails(TldDatabase::escape($pn));
        if (empty($itm)) {
            return 'Part does not exist';
        }
        if ($itm['t_csig'] === 'SUP') {
            return 'Part has been superseded';
        }
        $itm[0]['MIP'] = (float)$itm[0]['MIP'];
        foreach ($itm as $v) {
            if ($v['MIP'] > $itm[0]['MIP']) {
                $itm[0]['MIP'] = $v['MIP'];
            }
        }
        $info = array_merge($itm[0], $this->ePartsUser->getInvDetails(TldDatabase::escape($pn)));
        $this->add($pn, $info, $qty);
    }

    public function saveCart($name, $description)
    {
        $serialized_cart = TldDatabase::escape($this->serializeCart());
        $query = <<<EOF
		INSERT INTO eparts_favorites
		SET
			name='$name',
			description='$description',
			serialized_cart='$serialized_cart',
			parent_id={$this->ePartsUser->getID()},
			dt=NOW()
EOF;
        return tldUtils::sqlInsert($query);
    }

    public function serializeCart()
    {
        $array = [];
        $cart = $this->toArray();
        foreach ($cart['lines'] as $pn => $item) {
            $array[] = [
                'pn' => $pn,
                'qty' => $item['qty'],
            ];
        }
        return base64_encode(serialize($array));
    }

    public function unserializeCart($cart)
    {
        $string = base64_decode($cart);
        if ($string) {
            $array = unserialize($string);
            if (is_array($array)) {
                return $array;
            }
        }
        return [];
    }

    public function restoreCart(array $cart, $replaceCurrent = true)
    {
        if ($replaceCurrent) {
            $this->emptyAll();
        }
        foreach ($cart as $item) {
            if ($item['pn'] && $item['qty']) {
                $this->addCart($item['pn'], $item['qty']);
            }
        }
    }

    public function getFavorite($id)
    {
        $query = <<<EOF
		SELECT
			*
		FROM
			eparts_favorites
		WHERE
			parent_id={$this->ePartsUser->getID()} AND
			id=$id
EOF;
        $favorite = tldUtils::getSqlRowToAssocArray($query);
        $favorite['cart'] = $this->unserializeCart($favorite['serialized_cart']);

        return $favorite;
    }

    public function delFavorite($id)
    {
        $query = <<<EOF
		DELETE
		FROM
			eparts_favorites
		WHERE
			parent_id={$this->ePartsUser->getID()} AND
			id=$id
EOF;
        return tldUtils::sqlQuery($query);
    }

    public function listFavorites()
    {
        $query = <<<EOF
		SELECT
			*
		FROM
			eparts_favorites
		WHERE
			parent_id={$this->ePartsUser->getID()}
		ORDER BY
			name, id
EOF;
        return tldUtils::getSqlToAssocArray($query);
    }
}

class tldEpartsOrder
{
    public $itsHeader;
    public $itsID;

    public function __construct($id)
    {
        $this->itsID = $id;
        $this->itsHeader = $this->getHeader();
    }

    public function toArray()
    {
        $cart = $this->getCartArray();
        foreach ($cart['lines'] as $pn => $line) {
            $item = $line['item'];
            unset($line['item']);
            $items[$pn] = $item + $line;
        }
        return [
            'header' => $this->itsHeader,
            'items' => $items,
        ];
    }

    public function getHeader()
    {
        $query = <<<EOF
		SELECT o.*,
		CASE o.rush
			WHEN 1 THEN 'RUSH'
			ELSE 'NORMAL'
		END AS rush_description
		FROM eparts_orders o
		WHERE o.id=$this->itsID
EOF;
        return tldUtils::getSqlRowToAssocArray($query);
    }

    public function isEmpty()
    {
        return empty($this->itsHeader);
    }

    public function getID()
    {
        return $this->itsID;
    }

    public function getParentID()
    {
        return $this->itsHeader['parent_id'];
    }

    public function getCartArray()
    {
        return unserialize(trim($this->itsHeader['serialized_cart']));
    }

    public function insert($a, $cart)
    {
        if (empty($a)) {
            return 'Order parameters is empty';
        }
        if (empty($cart)) {
            return 'Cart is empty';
        }
        $serialized_cart = TldDatabase::escape(serialize($cart));
        $fields = ['parent_id', 'total', 'pono', 'name', 'email', 'phon', 'ccor', 'sdat', 'rush', 'cour', 'csvc', 'actn', 'ctxt', 'cdel', 'dadr', 'inst', 'ip_address'];
        $set = tldUtils::getSqlSet($a, $fields);
        $query = <<<EOF
		INSERT INTO eparts_orders
		SET
			dt=NOW(),
			serialized_cart='$serialized_cart',
			$set
EOF;
        return tldUtils::sqlInsert($query);
    }
}
