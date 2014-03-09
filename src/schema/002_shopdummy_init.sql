CREATE TABLE shop_products (
    p_id INT AUTO_INCREMENT NOT NULL,
    p_title VARCHAR(255) NOT NULL,
    p_ean VARCHAR(255) DEFAULT NULL,
    p_short_description VARCHAR(255) NOT NULL,
    p_long_description VARCHAR(255) NOT NULL,
    p_vendor VARCHAR(255) NOT NULL,
    p_vat DOUBLE PRECISION NOT NULL,
    p_price DOUBLE PRECISION NOT NULL,
    p_purchase_price DOUBLE PRECISION NOT NULL,
    p_currency VARCHAR(255) NOT NULL,
    p_delivery_date DATETIME DEFAULT NULL,
    p_availability INT NOT NULL,
    p_images LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)',
    p_category VARCHAR(255) NOT NULL,
    p_attributes LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
    p_delivery_workdays INT DEFAULT NULL,
    INDEX IDX_6802A0CCDB46A5E1 (p_category),
    PRIMARY KEY(p_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE TABLE shop_bepado_attributes (p_id INT NOT NULL,
    sba_bepado_shop_id VARCHAR(255) NOT NULL,
    sba_bepado_source_id VARCHAR(255) NOT NULL,
    INDEX IDX_2B3C905E62C18064D18AA3CE (sba_bepado_shop_id,
    sba_bepado_source_id),
    PRIMARY KEY(p_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE TABLE shop_bepado_exported (p_id INT NOT NULL,
    PRIMARY KEY(p_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
