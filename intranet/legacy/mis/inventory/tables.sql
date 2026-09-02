CREATE TABLE IF NOT EXISTS mis_inventory_assignments (
  id int(11) NOT NULL,
  dt date NOT NULL,
  poster_id int(11) NOT NULL,
  item_id int(11) NOT NULL,
  destination_type varchar(20) NOT NULL,
  destination_id int(11) NOT NULL,
  dt_from date NOT NULL,
  dt_to date NOT NULL,
  `comment` text NOT NULL,
  task_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS mis_inventory_brands (
  id int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  support_url varchar(250) NOT NULL,
  disable tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS mis_inventory_buildings (
  id int(11) NOT NULL,
  location_id int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  disable tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS mis_inventory_items (
  id int(11) NOT NULL,
  dt date NOT NULL,
  dt_warranty_end date NOT NULL,
  type_id int(11) NOT NULL,
  description text NOT NULL,
  brand_id int(11) NOT NULL,
  model varchar(100) NOT NULL,
  manufacturer_sn varchar(100) NOT NULL,
  tld_sn varchar(20) NOT NULL,
  state varchar(50) NOT NULL,
  buyer_bu_id int(11) NOT NULL,
  buyer_dpt_id int(11) NOT NULL,
  hidden tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS mis_inventory_item_types (
  id int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  category varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE mis_inventory_assignments
  ADD PRIMARY KEY (id);

ALTER TABLE mis_inventory_brands
  ADD PRIMARY KEY (id);

ALTER TABLE mis_inventory_buildings
  ADD PRIMARY KEY (id);

ALTER TABLE mis_inventory_items
  ADD PRIMARY KEY (id);

ALTER TABLE mis_inventory_item_types
  ADD PRIMARY KEY (id);


ALTER TABLE mis_inventory_assignments
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE mis_inventory_brands
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE mis_inventory_buildings
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE mis_inventory_items
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE mis_inventory_item_types
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;