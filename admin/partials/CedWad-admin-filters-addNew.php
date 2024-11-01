<?php
$editFilterId = isset($_GET['filterId']) ? $_GET['filterId'] : false;
$filterName = "";
$filterCat = "";
$filterKeyword = "";
if($editFilterId){
    $filterData = CedWadGetFilterData($editFilterId);
    $filterName = isset($filterData['name']) ? $filterData['name'] : "";
    $filetrId = isset($filterData['id']) ? $filterData['id'] : false;
    $filterMarkupData = isset($filterData['filter_data']) ? $filterData['filter_data'] : false;
    if($filterMarkupData){
        $filterMarkupData = json_decode($filterMarkupData, true);
        $filterCat = $filterMarkupData['cat'];
        $filterKeyword = $filterMarkupData['keyword'];
        $markupPrices = $filterMarkupData['priceMarkup'];
    }
}


?>
<div class="container">
    <div class="CedWad_main_wrapper">

        <!-- end intro section -->
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="CedWad_plugin__wrapper CedWad_filter_wrap filltering_wrap">
                    <div class="CedWad_list_content_wrapper CedWad_padding_main">
                        <div class="filtering_selection">
                            <div class="CedWad_heading"><h3><?php _e('Set keyword and category', 'CedWad'); ?></h3></div>
                            <ul>
                                <li class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><input type="text" placeholder="Enter Filter name" id = "CedWadFilterName" value = "<?php echo $filterName; ?>" ></li>
                                <li class="col-lg-4 col-md-4 col-sm-4 col-xs-12"><input type="text" placeholder="Enter Keyword" id = "CedWadAliKeyword" value="<?php echo $filterKeyword; ?>" ></li>
                                <li class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                    <div class="dropdown">

                                        <select aria-invalid="false" id="CedWadAliCat" class="form-control" id="a2w_category">
                                         
                                            <?php
                                            include('categories.php'); 
                                            if( is_array( $category ) && !empty( $category ) )
                                            {
                                                foreach ($category as $cat_id => $category_name) 
                                                {
                                                    ?>
                                                    <option <?php if( $cat_id == $filterCat ){ echo "selected"; } ?> value="<?php echo $cat_id; ?>"><?php echo $category_name; ?></option>
                                                    <?php    
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </li>
                                <div class="clear"></div>
                            </ul>
                        </div> 
                    </div>
                </div>

                <div class="row">
                </div>

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                       <div class="CedWad_plugin__wrapper CedWad_filter_wrap crd_pricing">
                        <div class="CedWad_list_content_wrapper CedWad_padding_main">
                            <div class="CedWad-advanced-prices">
                                <div class="CedWad_heading"><h3><?php _e('Set pricing rules', 'CedWad'); ?></h3></div>
                                <div class="CedWad_table-responsive">
                                    <table class="border">
                                        <thead>
                                            <tr class="border-bottom">
                                                <th colspan="4" >Cost range</th>
                                                <th>Markup</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if($editFilterId && $markupPrices){ 
                                                foreach($markupPrices as $key=>$value){ ?>
                                                <tr class="border-bottom">
                                                    <td>
                                                        <div class="input-group">        
                                                            <input class="form-control" value="<?php echo $value['min']; ?>" type="text" name="CedWad_cost_range_min[]"><span class="input-group-addon"> USD </span>
                                                        </div>
                                                    </td>
                                                    <td>-</td>
                                                    <td>
                                                        <div class="input-group">        
                                                            <input class="form-control" value="<?php echo $value['max']; ?>" type="text" name="CedWad_cost_range_max[]"><span class="input-group-addon"> USD </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                    </td>
                                                    <td>
                                                        <div class="input-group CedWad_priceMarkup_wrapper">
                                                            <input class="form-control value" value="<?php echo $value['amount']; ?>" name = "CedWad_markup_amount[]" type="text">
                                                            <?php $sign =  $value['sign']; ?>
                                                            <select class="CedWad_markup" name = "CedWad_markup_sign[]">
                                                                <option <?php if($sign == "+") echo "selected" ?> value="+"><?php _e('Add this in price', 'CedWad') ?></option>
                                                                <option <?php if($sign == "*") echo "selected" ?> value="*"><?php _e('Multiply this in price', 'CedWad') ?></option>
                                                                <option <?php if($sign == "=") echo "selected" ?> value="="><?php _e('Your own price', 'CedWad') ?></option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td>
                                                    </td>
                                                    <td class="CedWad_addMarkup_buttons">
                                                        <button class="CedWad_markupAdd">
                                                         <span class="glyphicon glyphicon-plus"><span>
                                                         </button>   
                                                         <button class="CedWad_markupDelete">
                                                            <span class="glyphicon glyphicon-minus"><span>
                                                            </button>             
                                                        </td>
                                                    </tr>
                                                    
                                                    <?php }
                                                }
                                                else{
                                                    ?>
                                                    <tr class="border-bottom">
                                                        <td>
                                                            <div class="input-group">        
                                                                <input class="form-control" value="0" type="text" name="CedWad_cost_range_min[]"><span class="input-group-addon"> USD </span>
                                                            </div>
                                                        </td>
                                                        <td>-</td>
                                                        <td>
                                                            <div class="input-group">        
                                                                <input class="form-control" value="10" type="text" name="CedWad_cost_range_max[]"><span class="input-group-addon"> USD </span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                        </td>
                                                        <td>
                                                            <div class="input-group CedWad_priceMarkup_wrapper">
                                                                <input class="form-control value" value="3" name = "CedWad_markup_amount[]" type="text">
                                                                <select class="CedWad_markup" name = "CedWad_markup_sign[]">
                                                                    <option value="+"><?php _e('Add this in price', 'CedWad') ?></option>
                                                                    <option value="*"><?php _e('Multiply this in price', 'CedWad') ?></option>
                                                                    <option value="="><?php _e('Your own price', 'CedWad') ?></option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td>
                                                        </td>
                                                        <td class="CedWad_addMarkup_buttons">
                                                            <button class="CedWad_markupAdd">
                                                                <span class="glyphicon glyphicon-plus"><span>
                                                                </button>             
                                                                <button class="CedWad_markupDelete">
                                                                    <span class="glyphicon glyphicon-minus"><span>
                                                                    </button> 
                                                                </td>
                                                            </tr>
                                                            <tr class="border-bottom">
                                                                <td>
                                                                    <div class="input-group">        
                                                                        <input class="form-control" value="0" type="text" name="CedWad_cost_range_min[]"><span class="input-group-addon"> USD </span>
                                                                    </div>
                                                                </td>
                                                                <td>-</td>
                                                                <td>
                                                                    <div class="input-group">        
                                                                        <input class="form-control" value="10" type="text" name="CedWad_cost_range_max[]"><span class="input-group-addon"> USD </span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                </td>
                                                                <td>
                                                                    <div class="input-group CedWad_priceMarkup_wrapper">
                                                                        <input class="form-control value" value="3" type="text" name = "CedWad_markup_amount[]">
                                                                        <select class="CedWad_markup" name="CedWad_markup_sign[]">
                                                                            <option value="+"><?php _e('Add this in price', 'CedWad') ?></option>
                                                                            <option value="*"><?php _e('Multiply this in price', 'CedWad') ?></option>
                                                                            <option value="="><?php _e('Your own price', 'CedWad') ?></option>
                                                                        </select>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                </td>
                                                                <td class="CedWad_addMarkup_buttons">
                                                                    <button class="CedWad_markupAdd">
                                                                        <span class="glyphicon glyphicon-plus"><span>
                                                                        </button>             
                                                                        <button class="CedWad_markupDelete">
                                                                            <span class="glyphicon glyphicon-minus"><span>
                                                                            </button> 
                                                                        </td>
                                                                    </tr>
                                                                    <?php } ?>
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr class="border-bottom">
                                                                        <td colspan="3">
                                                                           <lable><?php _e('All set to create a filter', 'CedWad'); ?></lable>
                                                                       </td>
                                                                       <td>
                                                                        <svg class="sign icon-plus  ">
                                                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-plus"/>
                                                                        </svg> 
                                                                    </td>
                                                                    <td>
                                                                     <?php if($editFilterId){?>
                                                                     <button type ="button" class ="button primary-button CedWad_push_filter_button" data-filterId = "<?php echo $editFilterId;  ?>" ><?php _e('Update your filter', 'CedWad'); ?></button>
                                                                     <?php }else{ ?>
                                                                     <button type ="button" data-filterId = "to-add" class ="button primary-button CedWad_push_filter_button" ><?php _e('Create your filter', 'CedWad'); ?></button>
                                                                     <?php } ?>
                                                                     <div class="CedWad_loader_save_filter" style="display: none">
                                                                        <img width="50" src="<?php echo CedWad_URL.'admin/images/loader.gif'; ?>">
                                                                    </div>
                                                                    <div class="CedWad_filter_success_msg">
                                                                        <span class="filter_success_msg_content"><?php _e( 'Data Saved Successfully!','CedWad' ); ?></span>
                                                                    </div>
                                                                    <div class="CedWad_filter_error_msg">
                                                                        <span class="filter_error_msg_content"><?php _e( 'Unable to Process Data!','CedWad' ); ?></span>
                                                                    </div>
                                                                </td>
                                                                
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>