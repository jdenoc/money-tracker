<link href="{{asset('components/bootstrap-material-design-icons/css/material-icons.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('css/entry-modal.css')}}" rel="stylesheet" type="text/css" />

<link href="{{asset('components/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('css/custom-bootstrap-switch.css')}}" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{asset('components/bootstrap-switch/dist/js/bootstrap-switch.min.js')}}"></script>

<link href="{{asset('components/bootstrap-tagsinput/dist/bootstrap-tagsinput.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('components/bootstrap-tagsinput/dist/bootstrap-tagsinput-typeahead.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('css/custom-bootstrap-tagsinput.css')}}" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{asset('components/bootstrap-3-typeahead/bootstrap3-typeahead.min.js')}}"></script>
<script type="text/javascript" src="{{asset('components/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js')}}"></script>

<link href="{{asset('components/jquery-file-upload/css/uploadfile.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('css/custom-jquery-uploadfile.css')}}" rel="stylesheet" type="text/css" />
<script type="text/javascript">var uploadToken = '{{ csrf_token() }}';</script>
<script type="text/javascript" src="{{asset('components/jquery-file-upload/js/jquery.uploadfile.min.js')}}"></script>

<!-- Entry Modal -->
<div class="modal fade" id="entry-modal" tabindex="-1" role="dialog" aria-labelledby="entry-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="entry-title">Entry: <span id="entry-id-display">new</span></h4>
                <input type="checkbox" id="entry-confirm" name="entry-confirm" />
            </div>
            <div class="modal-body">
                <label><span>Date:</span><input type="date" id="entry-date" name="entry-date" class="form-control"/></label>

                <label for="entry-value" id="entry-value-label">Value:</label>
                <div id="entry-value-input-group" class="input-group">
                    <div class="input-group-addon">$</div>
                    <input type="text" id="entry-value" name="entry-value" class="form-control" placeholder="9.99">
                </div>

                <label><span>Account Type:</span>
                    <select id="entry-account-type" name="entry-account-type" class="form-control">
                        <option></option>
                    </select>
                    <div id="entry-account-name" class="text-info hidden text-right">
                        <strong>Account Name:</strong>
                        <span></span>
                    </div>
                </label>

                <label><span id="entry-memo-label">Memo:</span><textarea id="entry-memo" name="entry-memo" class="form-control"></textarea></label>

                <div id="entry-expense-switch-container"><input type="checkbox" name="expense-switch" checked /></div>

                <a id="entry-tags-info" class="glyphicon glyphicon-info-sign text-info pull-right"></a>
                <label>
                    <span id="entry-tag-value">Tags:</span>
                    <input id="entry-tags" name="entry-tags" type="text" class="form-control" />
                </label>

                <div id="attachment-uploader">Upload</div>
                <input type="hidden" name="entry-new-attachments" id="entry-new-attachments" value="[]" />
            </div>

            <div class="modal-footer">
                <input type="hidden" name="entry-id" id="entry-id" />
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="entry-delete"><span class="glyphicon glyphicon-trash"></span>Delete</button>
                <button type="button" class="btn btn-default" id="entry-lock"><span class="mdi mdi-lock"></span></button>
                <button type="button" class="btn btn-default" id="entry-unlock"><span class="mdi mdi-lock-open"></span></button>
                <button type="button" class="btn btn-default" data-dismiss="modal" id="entry-close">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="entry-save"><span class="glyphicon glyphicon-ok"></span>Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- END - Entry Modal -->

<script type="text/javascript" src="{{asset('js/entry-modal.js')}}"></script>