<?php
/*
*	FulFillDirect Web Controller Side
*	File Name 			: 	ProductController.php
*	File Description	:	Product Controller to Manage Add, Edit and Search on Inventory for FulFillDirect
*	Created Date 		:	14 - April - 2015
*	Company				:	iExemplar
*	Created By			:	Manthiriyappan A
*	Contributors		:	Manthiriyappan A
**/


class ProductController extends \BaseController {

	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function saveNewProduct()
	{	
		$pro_dtl = new Products;
		$pro_dtl->Product_Name = Input::json('product');
		$pro_dtl->Product_Description = Input::json('product_des');
		$pro_dtl->Price =  Input::json('price');
		$pro_dtl->Image_URL = "1";
		$pro_dtl->Catelog_Id = "1";
		$pro_dtl->Channel_Id =  Input::json('channel');
		$pro_dtl->RefItemId = Input::json('ref_item_no');
		$pro_dtl->SKU = Input::json('sku');	
		$pro_dtl->UnitWeight =Input::json('unit_weight');			
		$pro_dtl->Active = 1;
//		$pro_dtl->CREATE_TS = date("Y-m-d H:i:s");
//		$pro_dtl->MODIFY_TS = date("Y-m-d H:i:s");
		$pro_dtl->save();			
	}
	
	public function productList(){
		$requested_ch_right ="AllowProductView";
		$usergroup = User::find(Auth::id())->usergroup()->first();
		$product_list = DB::select(DB::raw("SELECT CH.channel_name,PRO.id,PRO.Product_Name,
			PRO.Product_Description,PRO.Price,PRO.Catelog_Id,PRO.Channel_Id,PRO.RefItemId,PRO.SKU,
			PRO.UnitWeight FROM Products AS PRO
			LEFT JOIN Channels AS CH ON CH.id = PRO.Channel_Id
			WHERE PRO.ACTIVE =".'"1"'."  GROUP BY PRO.id
			HAVING PRO.Channel_id in(select ugchr.channel_id
										from user_group_channel_right ugchr, channel_right chr
										  where ugchr.userGroupID = ".$usergroup->id."
				 								and chr.channel_right_name = '".$requested_ch_right."')"));
		return Response::json(array('response' => $product_list));		
	}	
	
	public function getEditProductDetails(){
		$product_list = DB::select(DB::raw("SELECT PRO.id, PRO.Product_Name,
		PRO.Product_Description, PRO.Channel_Id,CH.Channel_Name, PRO.Price, 
		PRO.Image_URL,PRO.Catelog_Id,PRO.RefItemId,PRO.SKU,PRO.UnitWeight
		FROM Products AS PRO 
		INNER JOIN channels AS CH ON CH.id=PRO.Channel_Id
		WHERE PRO.id = ". Input::json('id') ));
		$channel_list = DB::table('channels')->get();
		return Response::json(array('product' => $product_list,'channel' => $channel_list));
	}
	
	public function editProductDetail(){
		$update_product_details = DB::select(DB::raw('update Products set
			Product_Name ='."'". Input::json('product')."'".',
			Product_Description ='."'". Input::json('product_des')."'".',
			Price = '."'". Input::json('price')."'".',
			RefItemId = '."'". Input::json('ref_item_id')."'".',
			SKU = '."'".Input::json('product_sku')."'".',
			UnitWeight = '."'".Input::json('unit_weight')."'".'
			where id = ' . Input::json('productid')));
		return Response::json(array('response' => "Succesfully Updated!!!"));
	}
	
	public function deleteProductDetail(){
		$update_product_details = DB::select(DB::raw('UPDATE Products SET `ACTIVE` = "0" WHERE id = ' . Input::json('productid')));
		$product_list = DB::select(DB::raw("SELECT PRO.id, PRO.Channel_Id, PRO.Stock_Count, PRO.Product_Name, CH.Channel_Name FROM PRODUCTS AS PRO
			INNER JOIN channels AS CH ON CH.id=PRO.Channel_Id
			WHERE PRO.ACTIVE ='1'")); 
		return Response::json(array('response' => $product_list));
	}
	
	public function getProductListCount(){
		$requested_ch_right ="AllowProductView";
		$usergroup = User::find(Auth::id())->usergroup()->first();
		$product_lists = DB::select(DB::raw("SELECT PRO.id, PRO.Channel_Id,
				CH.channel_name FROM PRODUCTS AS PRO
				INNER JOIN channels AS CH ON CH.id=PRO.Channel_Id WHERE PRO.ACTIVE ='1'
				and PRO.id in(select ugchr.Channel_id
										from user_group_channel_right ugchr, channel_right chr
										  where ugchr.userGroupID = ".$usergroup->id."
				 								and chr.channel_right_name = '".$requested_ch_right."')"));
		return Response::json(array('all_product_count' =>$product_lists));
	}

	public function allowProductAdd()
	{
		$requested_ch_right = "AllowProductAdd";
		$usergroup = User::find(Auth::id())->usergroup()->first();

		//select form usergroup channel rights where the channel_id and usergroup_id and ch_right
        $sql_right = "SELECT ugchr.* FROM user_group_channel_right ugchr, channel_right chr
				WHERE  ugchr.userGroupID = ".$usergroup->id."
					and ugchr.channel_right_Id =  chr.id
					and chr.channel_right_name = '".$requested_ch_right."'";
		$Right_list = DB::select(DB::raw($sql_right));

		return Response::json(count($Right_list) >0);
	}

	public function allowProductEditDelete()
	{
		$product_id = Input::json('id');
		$requested_ch_right ="AllowProductEditDelete";
		$usergroup = User::find(Auth::id())->usergroup()->first();
		$channel_id =Products::find($product_id)->Channel_Id;

		//select form usergroup channel rights where the channel_id and usergroup_id and ch_right
		$Right_list = DB::select(DB::raw(
			"SELECT ugchr.* FROM user_group_channel_right ugchr, channel_right chr
				WHERE ugchr.channel_id = ".$channel_id." and ugchr.userGroupID = ".$usergroup->id."
					and ugchr.channel_right_id =  chr.id
					and chr.channel_right_name = '".$requested_ch_right."'"));

		return Response::json(count($Right_list) >0);
	}
}
