ALTER TABLE `FastTraverse` ENGINE = InnoDB;
ALTER TABLE `FastTraverse` ADD CONSTRAINT `FastTraverse_Nodes_parent` FOREIGN KEY (`IdNode`) REFERENCES `Nodes`(`IdNode`) 
    ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `FastTraverse` ADD CONSTRAINT `FastTraverse_Nodes_child` FOREIGN KEY (`IdChild`) REFERENCES `Nodes`(`IdNode`) 
    ON DELETE CASCADE ON UPDATE CASCADE;
