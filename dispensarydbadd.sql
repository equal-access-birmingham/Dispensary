USE `eabdbw`;

ALTER TABLE `Prescriptions` ADD `PatientId` BIGINT UNSIGNED;
ALTER TABLE `Prescriptions` ADD CONSTRAINT `fk_Patients_PatientId` FOREIGN KEY (`PatientId`) REFERENCES `Patient` (`patientid`);

ALTER TABLE `PatientLabTests` ADD `PatientId` BIGINT UNSIGNED;
ALTER TABLE `PatientLabTests` ADD CONSTRAINT `fk_PatientLabTests_PatientId` FOREIGN KEY (`PatientId`) REFERENCES `Patient` (`patientid`);