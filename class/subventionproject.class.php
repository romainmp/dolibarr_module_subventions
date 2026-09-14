<?php
/* Copyright (C) 2026		Romain MP		<romain.mp@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/subventionproject.class.php
 * \ingroup     subventions
 * \brief       Class for SubventionProject (multi-project allocation / ventilation)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';


/**
 * Class SubventionProject
 *
 * Junction table between subventions and projects with allocated amounts.
 * Used by projet/element.php to show only the allocated portion per project.
 */
class SubventionProject extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'subventions';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'subventionproject';

	/**
	 * @var string Name of table without prefix where object is stored.
	 */
	public $table_element = 'subventions_subvention_projet';

	/**
	 * @var string String with name of icon.
	 */
	public $picto = 'fa-hand-holding-heart';


	/**
	 * @var int Subvention ID
	 */
	public $fk_subvention;

	/**
	 * @var int Project ID
	 */
	public $fk_project;

	/**
	 * @var float Allocated amount
	 */
	public $amount;

	/**
	 * @var float Total HT (alias for amount, used by projet/element.php)
	 */
	public $total_ht;

	/**
	 * @var float Total TTC (alias for amount, no VAT on subsidies)
	 */
	public $total_ttc;

	/**
	 * @var string Note
	 */
	public $note;

	/**
	 * @var string Date of creation
	 */
	public $datec;

	/**
	 * @var int User who created
	 */
	public $fk_user_creat;

	/**
	 * @var int User who modified
	 */
	public $fk_user_modif;


	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  int  $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int             Return integer <0 if KO, >0 if OK (rowid)
	 */
	public function create(User $user, $notrigger = 0)
	{
		$error = 0;

		// Validation
		if (empty($this->fk_subvention) || empty($this->fk_project)) {
			$this->error = 'ErrorMissingRequiredFields';
			return -1;
		}
		if ($this->amount < 0) {
			$this->error = 'ErrorNegativeAmount';
			return -1;
		}

		$this->db->begin();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element." (";
		$sql .= "fk_subvention, fk_project, amount, note, datec, fk_user_creat";
		$sql .= ") VALUES (";
		$sql .= " ".((int) $this->fk_subvention);
		$sql .= ", ".((int) $this->fk_project);
		$sql .= ", ".((float) $this->amount);
		$sql .= ", ".(!empty($this->note) ? "'".$this->db->escape($this->note)."'" : "NULL");
		$sql .= ", '".$this->db->idate(dol_now())."'";
		$sql .= ", ".((int) $user->id);
		$sql .= ")";

		dol_syslog(get_class($this)."::create", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
			$this->datec = dol_now();
			$this->fk_user_creat = $user->id;

			// Sync total_ht/total_ttc
			$this->total_ht = $this->amount;
			$this->total_ttc = $this->amount;

			if (!$error) {
				$this->db->commit();
				return $this->id;
			} else {
				$this->db->rollback();
				return -1;
			}
		} else {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param  int    $id  Id object
	 * @return int         Return integer <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id)
	{
		$sql = "SELECT rowid, fk_subvention, fk_project, amount, note, datec, tms, fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE rowid = ".((int) $id);

		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;
				$this->fk_subvention = $obj->fk_subvention;
				$this->fk_project = $obj->fk_project;
				$this->amount = $obj->amount;
				$this->total_ht = $obj->amount;
				$this->total_ttc = $obj->amount;
				$this->note = $obj->note;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->fk_user_creat = $obj->fk_user_creat;
				$this->fk_user_modif = $obj->fk_user_modif;

				$this->db->free($resql);
				return 1;
			} else {
				$this->db->free($resql);
				return 0;
			}
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  int  $notrigger 0=launch triggers after, 1=disable triggers
	 * @return int             Return integer <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = 0)
	{
		if ($this->amount < 0) {
			$this->error = 'ErrorNegativeAmount';
			return -1;
		}

		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
		$sql .= " amount = ".((float) $this->amount);
		$sql .= ", note = ".(!empty($this->note) ? "'".$this->db->escape($this->note)."'" : "NULL");
		$sql .= ", fk_user_modif = ".((int) $user->id);
		$sql .= " WHERE rowid = ".((int) $this->id);

		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			$this->total_ht = $this->amount;
			$this->total_ttc = $this->amount;
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param  User $user      User that deletes
	 * @param  int  $notrigger 0=launch triggers, 1=disable triggers
	 * @return int             Return integer <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = 0)
	{
		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE rowid = ".((int) $this->id);

		dol_syslog(get_class($this)."::delete", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Fetch all allocations for a given subvention
	 *
	 * @param  int   $subvention_id  Subvention ID
	 * @return SubventionProject[]|int  Array of objects or <0 if KO
	 */
	public function fetchAllBySubvention($subvention_id)
	{
		$records = array();

		$sql = "SELECT sp.rowid, sp.fk_subvention, sp.fk_project, sp.amount, sp.note, sp.datec,";
		$sql .= " sp.fk_user_creat, sp.fk_user_modif,";
		$sql .= " p.ref as project_ref, p.title as project_title";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as sp";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."projet as p ON p.rowid = sp.fk_project";
		$sql .= " WHERE sp.fk_subvention = ".((int) $subvention_id);
		$sql .= " ORDER BY sp.datec ASC";

		dol_syslog(get_class($this)."::fetchAllBySubvention", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->id = $obj->rowid;
				$record->fk_subvention = $obj->fk_subvention;
				$record->fk_project = $obj->fk_project;
				$record->amount = $obj->amount;
				$record->total_ht = $obj->amount;
				$record->total_ttc = $obj->amount;
				$record->note = $obj->note;
				$record->datec = $this->db->jdate($obj->datec);
				$record->fk_user_creat = $obj->fk_user_creat;
				$record->fk_user_modif = $obj->fk_user_modif;
				// Extra joined fields
				$record->project_ref = $obj->project_ref;
				$record->project_title = $obj->project_title;

				$records[$record->id] = $record;
				$i++;
			}
			$this->db->free($resql);
			return $records;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Get total allocated amount for a given subvention
	 *
	 * @param  int    $subvention_id  Subvention ID
	 * @return float|int  Total amount or <0 if KO
	 */
	public function getTotalVentilated($subvention_id)
	{
		$sql = "SELECT COALESCE(SUM(amount), 0) as total";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE fk_subvention = ".((int) $subvention_id);

		dol_syslog(get_class($this)."::getTotalVentilated", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			return (float) $obj->total;
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Return a link to the subvention card
	 *
	 * @param  int    $withpicto  Add picto into link
	 * @return string             HTML String with link
	 */
	public function getNomUrl($withpicto = 0)
	{
		dol_include_once('/custom/subventions/class/subvention.class.php');

		$subvention = new Subvention($this->db);
		if ($subvention->fetch($this->fk_subvention) > 0) {
			return $subvention->getNomUrl($withpicto);
		}
		return '';
	}
}
