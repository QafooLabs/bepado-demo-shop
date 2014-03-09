-- Table: Change (c)
CREATE TABLE IF NOT EXISTS `bepado_change` (
  `c_source_id` VARCHAR(64) NOT NULL,
  `c_operation` CHAR(8) NOT NULL,
  `c_revision` DECIMAL(20, 10) NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`c_source_id`),
  UNIQUE (`c_revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: Product (p)
CREATE TABLE IF NOT EXISTS `bepado_product` (
  `p_source_id` VARCHAR(64) NOT NULL,
  `p_hash` VARCHAR(64) NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`p_source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `bepado_change`
    ADD `c_product` BLOB NULL AFTER `c_revision`;

-- Table: Data (d)
CREATE TABLE IF NOT EXISTS `bepado_data` (
  `d_key` VARCHAR(32) NOT NULL,
  `d_value` VARCHAR(256) NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`d_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `bepado_change` MODIFY `c_product` LONGBLOB NULL;

-- Table: Shop Config (s)
CREATE TABLE IF NOT EXISTS `bepado_shop_config` (
  `s_shop` VARCHAR(32) NOT NULL,
  `s_config` BLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`s_shop`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: Reservations (r)
CREATE TABLE IF NOT EXISTS `bepado_reservations` (
  `r_id` VARCHAR(32) NOT NULL,
  `r_state` VARCHAR(12) NOT NULL,
  `r_order` LONGBLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`r_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `bepado_shop_config` CHANGE `s_config` `s_config` LONGBLOB NOT NULL;

-- Table: Shipping Costs (sc)
CREATE TABLE IF NOT EXISTS `bepado_shipping_costs` (
  `sc_shop` VARCHAR(32) NOT NULL,
  `sc_revision` VARCHAR(32) NOT NULL,
  `sc_shipping_costs` LONGBLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sc_shop`),
  INDEX (`sc_revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `bepado_shipping_costs`
    CHANGE COLUMN `sc_shop` `sc_from_shop` VARCHAR(32) NOT NULL,
    ADD COLUMN `sc_to_shop` VARCHAR(32) NOT NULL  AFTER `sc_from_shop`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`sc_from_shop`, `sc_to_shop`)
;

