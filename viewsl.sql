
CREATE OR REPLACE VIEW V_Stock AS 
    SELECT 
        Stock.id AS id,
        Stock.dateAjout AS dateAjout,
        Stock.dateModif AS dateModif,
        Stock.deleted AS deleted,
        Stock.quantity AS quantity,
        Stock.unitPrice AS unitPrice,
        Stock.comment AS `comment`,
        Stock.expiryDate AS expiryDate,
        Stock.manufacturingDate AS manufacturingDate,
        Stock.product AS product,
        (SELECT SUM(AuxiliaryStock.quantity) FROM AuxiliaryStock WHERE AuxiliaryStock.parent = Stock.id) AS served 
    FROM Stock;

CREATE OR REPLACE VIEW V_Product AS 
    SELECT 
        Product.id AS id,
        Product.dateAjout AS dateAjout,
        Product.dateModif AS dateModif,
        Product.deleted AS deleted,
        Product.name AS `name`,
        Product.defaultUnitPrice AS defaultUnitPrice,
        Product.description AS `description`,
        Product.picture AS picture,
        Product.category AS category,
        Product.packagingSize AS packagingSize,
        (SELECT (SUM(V_Stock.quantity) - SUM(V_Stock.served)) FROM V_Stock WHERE V_Stock.product = Product.id) AS available 
    FROM Product;

CREATE OR REPLACE VIEW V_AuxiliaryStock AS 
    SELECT 
        AuxiliaryStock.id AS id,
        AuxiliaryStock.dateAjout AS dateAjout,
        AuxiliaryStock.dateModif AS dateModif,
        AuxiliaryStock.deleted AS deleted,
        AuxiliaryStock.quantity AS quantity,
        AuxiliaryStock.parent AS parent,
        AuxiliaryStock.office AS office,
        Stock.expiryDate AS expiryDate,
        Stock.manufacturingDate AS manufacturingDate,
        Stock.product AS product,
        Stock.unitPrice AS unitPrice,
        (SELECT SUM(ProductOrdered.quantity) FROM ProductOrdered WHERE ProductOrdered.stock = AuxiliaryStock.id) AS served 
    FROM AuxiliaryStock INNER JOIN Stock ON Stock.id = AuxiliaryStock.parent;

CREATE OR REPLACE VIEW V_ProductOrdered AS
    SELECT 
        ProductOrdered.id AS id,
        ProductOrdered.dateAjout AS dateAjout,
        ProductOrdered.dateModif AS dateModif,
        ProductOrdered.deleted AS deleted,
        ProductOrdered.quantity AS quantity,
        ProductOrdered.reduction AS reduction,
        ProductOrdered.product AS product,
        ProductOrdered.command AS command,
        ProductOrdered.stock AS stock,
        V_AuxiliaryStock.unitPrice AS unitPrice,
        ((V_AuxiliaryStock.unitPrice - ProductOrdered.reduction) * ProductOrdered.quantity) AS amount
    FROM ProductOrdered INNER JOIN V_AuxiliaryStock ON V_AuxiliaryStock.id = ProductOrdered.stock;

CREATE OR REPLACE VIEW V_Command AS
    SELECT 
        Command.id  AS id,
        Command.dateAjout AS dateAjout,
        Command.dateModif AS dateModif,
        Command.deleted AS deleted,
        Command.deliveryDate  AS deliveryDate,
        Command.office AS office,
        Command.officeAdmin AS officeAdmin,
        Command.monthlyOrder AS monthlyOrder,
        Command.member AS member,
        Command.note AS note,

        (SELECT SUM(V_ProductOrdered.unitPrice) FROM V_ProductOrdered WHERE Command.id = V_ProductOrdered.command) AS totalUnitPrice,
        (SELECT (SUM(V_ProductOrdered.quantity)) FROM V_ProductOrdered WHERE Command.id = V_ProductOrdered.command) AS totalQantity,
        (SELECT (SUM(V_ProductOrdered.amount)) FROM V_ProductOrdered WHERE Command.id = V_ProductOrdered.command) AS amount
    FROM Command;-- INNER JOIN V_ProductOrdered ON Command.id = V_ProductOrdered.command;

CREATE OR REPLACE VIEW V_MonthlyOrder AS 
    SELECT 
        MonthlyOrder.id AS id,
        MonthlyOrder.dateAjout AS dateAjout,
        MonthlyOrder.dateModif AS dateModif,
        MonthlyOrder.deleted AS deleted,
        MonthlyOrder.disabilityDate AS disabilityDate,
        MonthlyOrder.member AS member,
        (
            SELECT (SUM(GradeMember.product) + SUM(GradeMember.membership) + SUM(GradeMember.officePart))
                FROM GradeMember WHERE GradeMember.monthlyOrder = MonthlyOrder.id
        ) AS used,
        (SELECT (SUM(V_Command.amount)) FROM V_Command WHERE V_Command.monthlyOrder = MonthlyOrder.id) AS amount
    FROM MonthlyOrder;-- INNER JOIN Command ON Command.monthlyOrder = MonthlyOrder.id;

CREATE OR REPLACE VIEW V_VirtualMoney AS
    SELECT DISTINCT
        VirtualMoney.id AS id,
        VirtualMoney.dateAjout AS dateAjout,
        VirtualMoney.dateModif AS dateModif,
        VirtualMoney.deleted AS deleted,
        VirtualMoney.amount AS amount,
        VirtualMoney.expected AS expected,
        VirtualMoney.product AS product,
        VirtualMoney.afiliate AS afiliate,
        VirtualMoney.office AS office,
        (
            SELECT (VirtualMoney.product - (SUM(MoneyGradeMember.product))) FROM MoneyGradeMember WHERE MoneyGradeMember.virtualMoney = VirtualMoney.id
        ) AS availableProduct,
        (
            SELECT (VirtualMoney.afiliate - (SUM(MoneyGradeMember.afiliate))) FROM MoneyGradeMember WHERE MoneyGradeMember.virtualMoney = VirtualMoney.id
        ) AS availableAfiliate,
        (
            SELECT SUM(MoneyGradeMember.product) FROM MoneyGradeMember WHERE MoneyGradeMember.virtualMoney = VirtualMoney.id
        ) AS usedProduct,
        (
            SELECT SUM(MoneyGradeMember.afiliate) FROM MoneyGradeMember WHERE MoneyGradeMember.virtualMoney = VirtualMoney.id
        ) AS usedAfiliate
    FROM VirtualMoney LEFT OUTER JOIN MoneyGradeMember ON VirtualMoney.id = MoneyGradeMember.virtualMoney;

CREATE OR REPLACE VIEW V_Office AS 
    SELECT DISTINCT
        Office.id AS id,
        Office.dateAjout AS dateAjout,
        Office.dateModif AS dateModif,
        Office.central AS central,
        Office.`name` AS `name`,
        Office.photo AS photo,
        Office.localisation AS localisation,
        Office.member AS member,
        Office.visible AS visible,
        (
            SELECT SUM(V_VirtualMoney.availableProduct) FROM V_VirtualMoney WHERE V_VirtualMoney.office = Office.id
        ) AS availableProduct,
        (
            SELECT SUM(V_VirtualMoney.availableAfiliate) FROM V_VirtualMoney WHERE V_VirtualMoney.office = Office.id
        ) AS availableAfiliate
            
    FROM Office LEFT JOIN V_VirtualMoney ON Office.id = V_VirtualMoney.office;