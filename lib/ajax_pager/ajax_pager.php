<?php
/***
 * Usage
 * new FilterFinder(
 *    $filter, [
 *    'pager' => new AjaxPager($this,
 *       ['page_size'] => 30
 *     )
 * ]);
 *
 * OR
 *
 * $pager = new AjaxPager($this, [...]);
 * list($limit, $offset) = $pager->nextPage();
 * if($pager->isXhrPaged()) {    // xhr request for paging called?
 *
 * support of form that sets order/default_limit
 * $pager = new AjaxPager($this, [], $form);
 * $pager->getOrder();        // returns order param (string, default '')
 * if($pager->isXhrOrdered()) {  // xhr request for ordering called?
 *
 ***/

class AjaxPager {

	public $options;
	public $params;
	public $controller;
	public $offset;
	public $limit;
	public $total;
	public $url;
	public $form;
	public $sorting;
	public $isXhrOrdered;
	public $order;
	public $isXhrPaged;

	static function OffsetName() {
		return defined("ATK14_PAGINATOR_OFFSET_PARAM_NAME") ? constant("ATK14_PAGINATOR_OFFSET_PARAM_NAME") : "from";
	}

	static function LimitName() {
		return defined("ATK14_PAGINATOR_COUNT_PARAM_NAME") ? constant("ATK14_PAGINATOR_COUNT_PARAM_NAME") : "count";
	}


	function __construct($controller, $options=[]) {
		$options += [
			"item_template" => null,     //Template for one item of paged lister
			"item_variable" => 'item',   //Variable name of paged item

			"name" => 'pager',           //Name of pager, used as param in XHR request idetifiing paging action
			"url" => null,                       //base url for pager links, given as array, null means detect it from controller
			"offset_name" => self::OffsetName(), //name of the param for offset
			"limit_name" => self::LimitName(),   //name of the param for limit

			"page_size" => null,          //size of one page
			"section_size" => null,       //page_size * pages_per_section - can be detected if form is given
			"pages_per_section" => 5,     //do not list more than x pages at once
			"first_page_shorter_by" => 0, //first page has less items than the following
			"total" => null,              //total number of paged items. Can be set later by $this->setTotal()
			"texts" => [],                //texts on buttons (see below)

			"form" => null,              //"ordering form" form that handle page_size and/or order
			"order_name" => 'order',     //order param name
			"page_size_name" => 'page_size', //size
			"paging_per" => "section",    //next/previous page lead to whole section, not page
			"empty_template" => "shared/ajax_pager/empty_list",
			"sorting" => null,
			'order_label' => _("Seřadit dle")
		];

		//Texts of buttons of pager
		$options['texts'] += [
			'first_page' => _("První strana"),
			'next/' =>  _("Další produkt"),
			'next/0' =>  _("Další strana"),
			'next/s' => _("Další %s produkty"),
			'next/ss' => _("Dalších %s produktů"),
			'next_page/' => _("Další strana"),
			'previous_page' => _("Předchozí strana"),
			'remain//' => _("Zbývá 1 položka z %t"),
			'remain/s/' => _("Zbývají %s položky z %t"),
			'remain/ss/' => _("Zbývá %s položek z %t"),

			'remain/0/' => "",
			'remain/0/s' => "",
			'remain/0/ss' => _("Vyhledáno celkem %t produktů"),

			'products/0' => _("produktů"),
			'products/' => _("produkt"),
			'products/s' => _("produkty"),
			'products/ss' => _("produktů"),

			'loading' => _("Načítám...")
		];

		$this->options = $options;
		$this->params = $controller->params;
		$this->controller = $controller;

		$this->offset = max((int)$this->params[$options['offset_name']],0);
		$this->limit = max((int)$this->params[$options['limit_name']],0);
		$this->total = $options['total'];

		// HACK: Yarriho oprava
		if(!$this->limit){ // $this->limit totiz tady byva i 0!
			$this->limit = $options["page_size"];
		}

		$this->url = $this->removePagingParams($options['url']);
		$this->form = $options['form'];
		if($this->sorting = $options['sorting']) {
			if(!$this->form) {
				$this->form = new Atk14Form();
			}
			$keys = $this->form->get_field_keys();
			if(!in_array($options['order_name'],$keys)) {
					$this->form->add_field($options['order_name'], new ChoiceField([
							 "label" => $options['order_label'],
							 "choices" => [],
							 "initial" => $this->sorting->getIterator()->current(),
							 "required" => false,
				 ]));
			}
			$this->updateFormBySorting($options['form']);
		}

		$this->updateByForm($options['form']);

		if( $this->options['page_size'] === null ) {
			$this->options['page_size'] = 30;
		} else {
			$this->options['page_size'] = (int) $this->options['page_size'];
		}

		if( $this->options['section_size'] === null) {
			$this->options['section_size'] = $this->getPageSize() * $this->options['pages_per_section'];
		}

		$this->isXhrPaged = $this->params[$this->options['name']] && $this->controller->request->xhr();

		if($this->limit) {
				if($this->options['section_size']) {
					$this->limit = min($this->limit, $this->options['section_size']);
				}
		} else {
				//this condition should take care of Back
			if(!$this->offset && $this->isXhrPaged() && $this->options['paging_per'] == 'section') {
					$this->limit = $this->sectionSize();
				} else {
					$this->limit = $this->getPageSize();
				}
				if(!$this->offset) {
					$this->limit -= $this->options['first_page_shorter_by'];
				};
		}

	}

	function updateFormBySorting() {
		$field = $this->form->fields[$this->options['order_name']];
		$choices = $field->get_choices();
		foreach($this->sorting as $s) {
			if(!key_exists($s, $choices)) {
				$choices[$s] = $this->sorting->getTitle($s);
			}
		}
		$field->set_choices($choices);
	}

	function getFormId() {
		if(!$this->form) { return null; };
		return $this->form->get_attr('id');
	}

	function jsUpdate() {
		$out = [
			'offset' => (int) $this->getOffset(),
			'total' => (int) $this->getTotal(),
			'sectionSize' => (int) $this->sectionSize(),
			'pageSize' => $this->getPageSize(),
			'pagingPer'=> $this->pagingPer(),
			'url' => $this->baseUrl(),
		];
		return json_encode($out);

	}

	function jsData() {
		$out = [
			'offset' => (int) $this->getOffset(),
			'offsetName' => $this->options['offset_name'],
			'limitName' => $this->options['limit_name'],
			'total' => (int) $this->getTotal(),
			'texts' => $this->options['texts'],
			'url' => $this->baseUrl(),
			'sectionSize' => (int) $this->sectionSize(),
			'pageSize' => $this->getPageSize(),
			'pagingPer'=> $this->pagingPer(),
			'form' => $this->getFormId()
		];
		return json_encode($out);
	}

	/**
	 * Set the total number of paged items.
	 **/
	function setTotal($total) {
		$this->total = $total;
	}

	/**
	 * Url functions
	 */
	function nextPageUrl() {
		return $this->buildUrl($this->nextPage());
	}

	function previousPageUrl() {
		return $this->buildUrl($this->previousPage());
	}

	/**
	 * Just $this->url passed through $this->controller->_link_to
	 **/
	function baseUrl() {
		return $this->buildUrl([null, null]);
	}

	function firstPage() {
		$shift = $this->options['paging_per'] == 'section'?
							$this->sectionSize():
							$this->getPageSize();
		return $this->offset?['limit' => $shift]:null;
	}

	function firstPageUrl() {
		return $this->buildUrl($this->firstPage());
	}

	function previousPage() {
		$ss = $this->sectionSize();
		if($this->offset <= $ss) {
			return null;
		}
		$limit = null;
		$shift = $this->options['paging_per'] == 'section'?$ss:$this->getPageSize();

		$offset = $this->offset - $shift;
		if($offset <= -$this->options['first_page_shorter_by']) {
			$limit-=$this->options['first_page_shorter_by'];
		}
		$offset = max($offset, 0);
		if($shift > $this->getPageSize()) {
			return array($offset, $shift);
		}
		return array($offset, null);
	}


	/**
	 Return params for next page, or null if no next page is available
	 */
	function nextPage() {
		if($this->getRemains()<=0) {
			return null;
		}
		$offset = $this->offset + $this->limit;
		$ss = $this->sectionSize();
		if($this->limit + $this->getPageSize() > $ss) {
			//new page - set the limit according to 'paging_per' parameter
			$limit = $this->options['paging_per'] == 'section'?$ss:null;
		} else {
			//fetch next items
			$limit = null;
		}
		return array($offset, $limit);
	}


	/**
	 * Add limit and offset params to url. Null in, null out.
	 **/
	function buildUrl($params = null) {
		$url = $this->url;
		if($params) {
			if($params[0]) {
				$url[$this->options['offset_name']] = $params[0];
			}
			if($params[1]) {
				$url[$this->options['limit_name']] = $params[1];
			}
			return $this->controller->_link_to($url);
		} else {
			return '';
		}
	}

	/**
	 * Functions used in template and simple getters
	 */
	function getItemTemplate() {
		return $this->options['item_template'];
	}

	function getEmptyTemplate() {
		return $this->options['empty_template'];
	}

	function setItemTemplate($template) {
		return $this->options['item_template'] = $template;
	}

	function getItemVariable() {
		return $this->options['item_variable'];
	}

	function getName() {
		return $this->options['name'];
	}

	function ammountLeft() {
		return max(0,$this->getTotal() - $this->offset - $this->limit);
	}

	function getTotal() {
		return $this->total;
	}

	function getRemains() {
		return max(0, $this->getTotal() - $this->offset - $this->limit);
	}

	function pagingPer() {
		return $this->options['paging_per'];
	}

	/*** Maximum number of items fetched at once, items can be added by
		"infinite scrolling" up to sectionSize() ***/
	function getPageSize() {
		return $this->options['page_size'];
	}

	function pageSize() {
		return $this->getPageSize();
	}

	/*** Maximum number of items that can be readed on one page **/
	function sectionSize() {
		return $this->options['section_size'];
	}

	function getLimit() {
		return $this->limit;
	}

	function getOffset() {
		return $this->offset;
	}

	function getLimitOffset() {
		return ['limit' => $this->limit, 'offset' => $this->offset ];
	}

	/**
	 * If Xhr request for paging/ordering is given, set the controller
	 * template and necessary template vars to paging action.
	 **/
	function xhr($finder) {
			$this->setTotal($finder->getRecordsCount());
			if(!$this->isXhr()) return false;
			$this->controller->render_layout = false;
			$this->controller->template_name = 'shared/ajax_pager/_ajax_pager_xhr';
			$this->controller->tpl_data['finder'] = $finder;
			$this->controller->tpl_data['pager'] = $this;
			return true;
	}

	/**
	 * Get the text for propper amount of items
	 * $this->getText('next');
	 * > 'Next product' / 'Dalsi produkt'
	 * $this->getText('next',2);
	 * > 'Next 2 products' / 'Dalsi 2 produkty'
	 * $this->getText('next',8);
	 * > 'Next 8 products' / 'Dalsich 8 produktu'
	 *
	 * The texts must be defined with names $name, $name.'s' and $name.'ss'
	 **/
	function getText($name) {
		$add = [$name];
		$char = ord('s');
		$replace = [];

		foreach(array_slice(func_get_args(),1) as $amount) {
			if($amount <=0) {
				$add[] = "0";
			} elseif($amount > 1) {
				if($amount > 4) {
					$add[] = "ss";
				} else {
					$add[] = "s";
				}
			} else {
				$add[]='';
			}
			$replace["%".chr($char)] = $amount;
			$char++;
		}

		for($r=count($add);$r>=0;$r--) {
			$gname = implode('/', $add);
			if(key_exists($gname, $this->options['texts'])) {
				return str_replace(array_keys($replace), $replace, $this->options['texts'][$gname]);
			}
			//from the last argument, replace the counter suffix with ''
			if($r==1) break;
			$add[$r-1] = '';
		}

		return $gname;
	}

	/**
	 * Returns params without all paging params
	 * $this->removePagingParams(['a' => 1, 'limit' => 5]);
	 * > ['a' => 1]
	 */
	function removePagingParams($params = null) {
		if($params === null) {
			$params = $this->params->toArray();
		}
		return array_diff_key($params, [
			$this->options['limit_name'] => 1,
			$this->options['offset_name'] => 1,
			$this->options['order_name'] => 1,
			$this->options['page_size_name'] => 1,
			$this->options['name'] => 1,
		]);
	}

	/***
		return url without paging and order params
	 */
	function getUrl() {
		return $this->url;
	}

	/**
	 * Update page_size and section_size (the later if it is not set explicitly)
	 * by the choices given in form (section_size is just the largest value)
	 * Also, set the url (action) of the form.
	 * If form was not submited, use values cached in session instead, or
	 * form default values as last resort.
   */
	function updateByForm() {
		if($this->form) {
			$this->form->set_action($this->url);

			$data = $this->form->validate($this->params);
			if(is_null($data)){
				// Toto je Yarriho fix. Melo by se pocitat s tim, ze se z validace muze vratit null!
				$data = [];
			}
			if($data) {
				$data = array_filter($data);
			}
			$this->isXhrOrdered = $data && $this->controller->request->xhr();
			$sessionParam = 'pager:'.$this->options['name'];
			if($data) {
				$this->controller->session->s($sessionParam, $data);
			} elseif($_data = $this->controller->session->g($sessionParam)) {
				$data = $this->form->validate($_data);
			}
			if(is_null($data)){
				$data = []; // Yarri: je dulezite mit i tady jistotu, ze mame pole, jinak by radek '$data += $form->get_initial();' mohl zpusobit Fatal Error
			}
			$data += $this->form->get_initial();
			if(isset($data[$this->options['page_size_name']])) {
				$this->options['page_size'] = (int) $data[$this->options['page_size_name']];

				if(!$this->options['section_size']) {
					$field = $this->form->fields[$this->options['page_size_name']];
					if($field) {
						$this->options['section_size'] = max(array_keys($field->get_choices()));
					}
				}
			}
			$this->order = $data[$this->options['order_name']];
		} else {
			$this->isXhrOrdered = false;
			$this->order = null;
			$data = null;
		}
		$this->updateByFormHook($data);
	}

	function updateByFormHook($data) {}

	function getOrder() {
		return $this->order;
	}

	/**
	 *
	 * @return Atk14Sorting
	 */
	function getSorting() {
		if(!$this->sorting) {
			$this->sorting = new Atk14Sorting(
				new Dictionary([ATK14_SORTING_PARAM_NAME => $this->getOrder()])
			);
		}
		return $this->sorting;
	}

	function getSqlOrder() {
		return $this->getSorting()->getOrder($this->getOrder());
	}

	/***
	 * Současný request je XHR requestem pro stránkování
	 * @return bool
	 ***/
	function isXhrPaged() {
		return $this->isXhrPaged;
	}

	/***
	 * Současný request je XHR requestem pro řazení
	 * @return bool
	 ***/
	function isXhrOrdered() {
		return $this->isXhrOrdered;
	}

	/***
	 * Současný request je XHR requestem řešený pagerem, tedy stránkování
	 * nebo řazení
	 * @return bool
	 ***/
	function isXhr() {
		return $this->isXhrPaged() or $this->isXhrOrdered();
	}
}
