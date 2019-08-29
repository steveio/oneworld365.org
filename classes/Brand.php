<?php



class Brand {
	
	private $brand_name;
	private $logo_url;
	
	public $site_id; // also brand id ( PK from database website table )
	private $site_title;
	private $site_description;

	private $website_url;
	private $website_name;

	private $admin_website_url;
	private $company_base_url;	
	
	private $default_country_id;
	private $default_activity_id;
	private $default_category_id;

	private $site_categories;
	
	private $default_company_profile_type_id;  // int id of default profile type for this brand
	private $available_company_profile_type_id; // array ids of available profile types for this brand

	private $placement_title;
	private $company_title;
	
	private $full_desc_label;
	
	/*
	 * Whether to display category, activity & country selectors
	 */
	private $display_cat_act_cty = TRUE;
	
	public function __Construct($aBrandConfig) {

		$this->brand_name = $aBrandConfig['brand_name'];
		$this->logo_url = $aBrandConfig['logo_url'];
		$this->site_id = $aBrandConfig['site_id'];
		$this->site_title = $aBrandConfig['site_title'];
		
		$this->website_url = $aBrandConfig['website_url'];
		$this->website_path = $aBrandConfig['website_path'];
		$this->website_name = $aBrandConfig['website_name']; // eg seasonaljobs365.com

		$this->admin_website_url = $aBrandConfig['admin_website_url'];
		$this->company_base_url = $aBrandConfig['company_base_url'];
		
		
		$this->default_company_profile_type_id = $aBrandConfig['default_company_profile_type_id'];
		$this->default_placement_profile_type_id = $aBrandConfig['default_placement_profile_type_id'];
		
		$this->available_company_profile_type_id = isset($aBrandConfig['available_company_profile_type_id']) ? $aBrandConfig['available_company_profile_type_id'] : array();

		$this->default_country_id = $aBrandConfig['default_countries'];
		$this->default_activity_id  = $aBrandConfig['default_activities'];
		$this->default_category_id = $aBrandConfig['default_categories'];
		
		$this->site_categories = $aBrandConfig['site_categories'];
		
		$this->placement_title = $aBrandConfig['placement_title'];
		$this->company_title = $aBrandConfig['company_title'];
		
		if (isset($aBrandConfig['full_desc_label'])) {
			$this->full_desc_label = $aBrandConfig['full_desc_label'];
		}

		$this->display_cat_act_cty = $aBrandConfig['display_cat_act_cty'];
		
	}

	public function GetBrandName() {
		return $this->brand_name;
	}
	
	
	public function GetName() {
		return $this->brand_name;
	}
	
	public function GetLogoUrl() {
		return $this->logo_url;
	}
	
	public function GetSiteId() {
		return $this->site_id;
	}
	

	public function GetSiteTitle() {
		return $this->site_title;
	}
	
	public function GetSiteDescription() {
		return $this->site_description;
	}
	
	public function GetDefaultCompanyProfileTypeId() {
		return $this->default_company_profile_type_id;
	}
	
	public function GetWebsiteUrl() {
		return $this->website_url;
	}

	public function GetWebsitePath() {
		return $this->website_path;
	}

	public function GetWebsiteName() {
		return $this->website_name;
	}
	
	public function GetDefaultPlacementProfileTypeId() {
		return $this->default_placement_profile_type_id;
	}
	
	public function GetAvailableCompanyProfileTypeId() {
		return $this->available_company_profile_type_id;
	}
	
	public function GetDefaultCountryId() {
		return $this->default_country_id;
	}
	
	public function GetDefaultActivityId() {
		return $this->default_activity_id;
	}
	
	public function GetDefaultCategoryId() {
		return $this->default_category_id;
	}	

	public function GetSiteCategories() {
		return $this->site_categories;	
	}
	
	public function GetPlacementTitle() {
		return $this->placement_title;
	}

	public function GetCompanyTitle() {
		return $this->company_title;
	}
	
	public function GetDisplayCatActCtyOptions() {
		return $this->display_cat_act_cty;
	}

	public function GetFullDescLabel() {
		return $this->full_desc_label;
	}

	public function GetAdminWebsiteUrl() {
		return $this->admin_website_url;
	}
	
	public function GetCompanyBaseUrl() {
		return $this->company_base_url;
	}
	
}