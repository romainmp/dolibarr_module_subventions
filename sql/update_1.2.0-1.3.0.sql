-- Copyright (C) 2026 Radio Saint Féréol / Romain MP
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

-- Migration 1.2.0 to 1.3.0

-- Table llx_c_subventions_financeur
ALTER TABLE llx_c_subventions_financeur ADD COLUMN accountancy_code_receivable varchar(32) DEFAULT NULL AFTER accountancy_code;

UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4410' WHERE ref = 'SF_AUTRE' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4411' WHERE ref = 'SF_ETAT' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4412' WHERE ref = 'SF_REG' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4413' WHERE ref = 'SF_DEP' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4414' WHERE ref = 'SF_COM' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4418' WHERE ref = 'SF_SOC' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4415' WHERE ref = 'SF_EUR' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4416' WHERE ref = 'SF_ASP' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4418' WHERE ref = 'SF_PUB' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');
UPDATE llx_c_subventions_financeur SET accountancy_code_receivable = '4419' WHERE ref = 'SF_PRI' AND (accountancy_code_receivable IS NULL OR accountancy_code_receivable = '');

-- Table llx_subventions_financement
ALTER TABLE llx_subventions_financement ADD COLUMN accounted tinyint DEFAULT 0;
ALTER TABLE llx_subventions_financement ADD COLUMN date_engagement date DEFAULT NULL;
ALTER TABLE llx_subventions_financement ADD COLUMN fk_bookkeeping_receivable integer DEFAULT NULL;
ALTER TABLE llx_subventions_financement ADD COLUMN fk_bookkeeping_product integer DEFAULT NULL;

-- Table llx_subventions_paiement
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_bank integer DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_account integer DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_paiement integer DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN num_paiement varchar(50) DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN accounted tinyint DEFAULT 0;
ALTER TABLE llx_subventions_paiement ADD COLUMN date_engagement date DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_bookkeeping_bank integer DEFAULT NULL;
ALTER TABLE llx_subventions_paiement ADD COLUMN fk_bookkeeping_receivable integer DEFAULT NULL;
