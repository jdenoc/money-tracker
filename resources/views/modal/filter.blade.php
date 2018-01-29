<link href="{{asset('components/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('css/custom-bootstrap-switch.css')}}" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{asset('components/bootstrap-switch/dist/js/bootstrap-switch.min.js')}}"></script>

<link href="{{asset('css/filter-modal.css')}}" rel="stylesheet" type="text/css" />

<!-- Filter Modal -->
<div class="modal fade" id="filter-modal" tabindex="-1" role="dialog" aria-labelledby="filter-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="filter-title">Search Filter</h4>
            </div>
            <div class="modal-body">
                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-start-date" class="pull-left"><span>Start Date:</span></label>
                    <input type="date" name="filter-start-date" id="filter-start-date" class="form-control pull-right"/>
                </div>
                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-end-date" class="pull-left"><span>End Date:</span></label>
                    <input type="date" name="filter-end-date" id="filter-end-date" class="form-control pull-right"/>
                </div>

                <div class="filter-modal-element center-block clearfix">
                    <input type="checkbox" id="filter-toggle-account-or-account-type" class="pull-right" />
                    <select id="filter-account-or-account-type" class="form-control pull-right">
                        <option value="">[ ALL ]</option>
                    </select>
                </div>

                <div class="filter-modal-element center-block clearfix">
                    <label class="pull-left">Tags:</label>
                    <div class="btn-group" data-toggle="buttons" id="filter-tags"></div>
                </div>

                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-income" class="pull-left">Income:</label>
                    <input type="radio" name="filter-expense" id="filter-income" class="pull-right" value="0" />
                </div>
                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-expense" class="pull-left">Expense:</label>
                    <input type="radio" name="filter-expense" id="filter-expense" class="pull-right" value="1" />
                </div>

                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-has-attachments" class="pull-left"><span>Has Attachment(s):</span></label>
                    <input type="radio" name="filter-attachments" id="filter-has-attachments" class="pull-right" value="1"/>
                </div>
                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-no-attachments" class="pull-left"><span>No Attachment(s):</span></label>
                    <input type="radio" name="filter-attachments" id="filter-no-attachments" class="pull-right" value="0"/>
                </div>

                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-unconfirmed" class="pull-left">Not Confirmed:</label>
                    <input type="checkbox" name="filter-unconfirmed" id="filter-unconfirmed" class="pull-right"/>
                </div>

                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-min-value" class="pull-left">Min Range:</label>
                    <div class="input-group filter-min-max-input-group pull-right">
                        <div class="input-group-addon">$</div>
                        <input type="text" id="filter-min-value" name="filter-min-value" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="filter-modal-element center-block clearfix">
                    <label for="filter-max-value" class="pull-left">Max Range:</label>
                    <div class="input-group filter-min-max-input-group pull-right">
                        <div class="input-group-addon">$</div>
                        <input type="text" id="filter-max-value" name="filter-max-value" class="form-control" placeholder="100.00">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="filter-close">Close</button>
                <button type="button" class="btn btn-warning" id="filter-reset"><span class="glyphicon glyphicon-repeat"></span> Reset</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="filter-set"><span class="glyphicon glyphicon-search"></span> Set Filter</button>
            </div>
        </div>
    </div>
</div>
<!-- END - Filter Modal -->

<script type="text/javascript" src="{{asset('js/filter-modal.js')}}"></script>