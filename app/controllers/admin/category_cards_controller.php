<?php
class CategoryCardsController extends AdminController{

	// pridani produktu do kategorie probiha taky v cards/add_to_category

	function index() {
		$this->page_title = sprintf(_("Products in category %s"),strip_tags($this->category->getName()));
		$this->breadcrumbs[] = _("Product list");

		($d = $this->form->validate($this->params)) || ($d = $this->form->get_initial());

		$format = $this->params->getString("format");
		if($format && !in_array($format,["csv"])){
			return $this->_execute_action("error404");
		}

		$bind_ar = [];
		$bind_ar[":category_id"] = $this->category;

		$search_condition = "";
		if($d["search"]){
			$_fields = [];
			$_fields[] = "(SELECT STRING_AGG(catalog_id,' ') FROM products WHERE card_id=cards.id)";
			$_fields[] = "(SELECT body FROM translations WHERE table_name='cards' AND record_id=cards.id AND key='name' AND lang=:lang)";
			$bind_ar[":lang"] = $this->lang;
			$_fields = array_map(function($_f){ return "COALESCE($_f,'')"; },$_fields);
			if($ft_cond = FullTextSearchQueryLike::GetQuery("UPPER(".join("||' '||",$_fields).")",Translate::Upper($d["search"]),$bind_ar)){
				$search_condition = $ft_cond;
			}
		}

		$_rank_asc = "category_cards.rank ASC, category_cards.id ASC";
		$_rank_desc = "category_cards.rank DESC, category_cards.id DESC";
		$_catalog_id = "COALESCE((SELECT catalog_id FROM products WHERE card_id=cards.id AND NOT deleted ORDER BY catalog_id ASC LIMIT 1),'')";
		$this->sorting->add("rank",$_rank_asc,$_rank_desc);
		$this->sorting->add("created_at","cards.created_at DESC, $_rank_asc","cards.created_at ASC, $_rank_desc");
		$this->sorting->add("catalog_id","$_catalog_id ASC, $_rank_asc","$_catalog_id DESC, $_rank_desc");

		$this->tpl_data["finder"] = $finder = Card::Finder(array(
			"query" => "
				SELECT
					category_cards.card_id
				FROM
					category_cards,
					cards
				WHERE
					category_cards.category_id=:category_id AND
					cards.id=category_cards.card_id
				".($search_condition ? "AND ($search_condition)" : "")."	
			",
			"order_by" => $this->sorting,
			"bind_ar" => $bind_ar,
			"offset" => $format ? null : $this->params->getInt("offset"),
			"limit" => $format ? null : 100,
			"use_cache" => true,
		));

		$this->tpl_data["searching"] = $searching = strlen($search_condition)>0;

		// building URL for CSV export
		$params = $this->params->toArray();
		unset($params["offset"]);
		$params["format"] = "csv";
		$this->tpl_data["csv_export_url"] = $this->_link_to($params);


		if($format=="csv"){
			$csv = new CsvWriter();
			$this->response->setContentType("text/csv");
			$this->response->setHeader('Content-Disposition: attachment; filename="category_cards.'.$format.'"');
			$this->render_template = false;
			$i = 1;
			foreach($finder->getRecords() as $card){
				$rank = "";
				if(!$searching && $this->sorting->getActiveKey()=="rank"){
					$rank = $i;
				}
				if(!$searching && $this->sorting->getActiveKey()=="rank-desc"){
					$rank = $finder->getTotalAmount() - $i + 1;
				}
				$product = $card->getFirstProduct();
				$csv[] = [
					"catalog_id" => $product ? $product->getCatalogId() : "",
					"name" => $card->getName(),
					"rank" => $rank,
					"created_at" => $card->getCreatedAt(),
				];
				$i++;
			}

			$this->response->write($csv->writeToString(["format" => $format, "with_header" => true]));
		}


	}

	function create_new(){
		$this->page_title = sprintf(_("Adding product into the category %s"),strip_tags($this->category->getName()));

		$this->_save_return_uri();

		if($this->request->post() && ($d = $this->form->validate($this->params))){
			$this->category->addCard($d["card_id"]);

			$this->flash->success(_("The product was added"));
			$this->_redirect_back();
		}
	}

	function edit(){
		$this->page_title = sprintf(_("Edit ranking of %s"),$this->card->getName());

		$this->_save_return_uri();
		$this->request->get() && $this->form->set_initial($this->params);
		if($this->request->post() && ($d = $this->form->validate($this->params))){
			$this->category->getCardsLister()->setRecordRank($this->card,$d["rank"]-1);
			$this->_redirect_back();
		}
	}

	function set_rank(){
		if(!$this->request->post()){ return $this->_execute_action("error404"); }

		$this->category->getCardsLister()->setRecordRank($this->card,$this->params->getInt("rank"));

		$this->render_template = false;
	}

	function destroy(){
		if(!$this->request->post()){ return $this->_execute_action("error404"); }

		$this->category->removeCard($this->card);

		$this->template_name = "application/destroy";
	}

	function _before_filter(){
		if(in_array($this->action,array("set_rank","destroy","edit"))){
			$this->_find("category","category_id");
			$card_id_key = $this->params->defined("card_id") ? "card_id" : "id"; // "id" posila js udelatko pro trideni
			$this->_find("card",$card_id_key);
		}

		if(in_array($this->action,array("create_new","index"))){
			$this->_find("category","category_id");
		}

		if(in_array($this->action,array("index","create_new","edit")) && isset($this->category)){
			$this->_add_category_to_breadcrumbs($this->category);
		}
	}
}
