<?php
/**
 * File old.phtml.php
 *
 * since: 09.07.2022
 * author: alexej@kisselev.de
 */

$columns = $this->columns;
/** @var UserConfig $currentConfig */
$currentConfig = $this->currentConfig;
if ($currentConfig !== null) {
    $currentColumns = $currentConfig->getColumns();
} else {
    $currentColumns = $columns;
}

?>
<div id="columnActions" class="row tab-pane form-vertical form-actions"<?php echo devhelp(__FILE__) ?>>

    <div class="col-md-10">
        <?php if (empty($readonly)): ?>
            <div class="btn-group">
                <a class="btn btn-sm btn-success" href="#" data-action="select">
                    <i class="fa check-square-o"></i> <?= $this->translate('select all') ?>
                </a>
            </div>

            <div class="btn-group">
                <a class="btn btn-sm btn-default" href="#" data-action="deselect">
                    <i class="fa fa-square-o"></i> <?= $this->translate('deselect all') ?>
                </a>
            </div>
        <?php endif; ?>
        <div class="btn-group" style="margin-left: 40px">
            <a class="btn btn-sm btn-primary" href="#" data-action="all">
                <?= $this->translate('show all'); ?>
            </a>
        </div>
        <div class="btn-group">
            <a class="btn btn-sm btn-default" href="#" data-action="checked">
                <i class="fa fa-check-square-o"></i> <?= $this->translate('show checked') ?>
            </a>
        </div>

        <div class="btn-group">
            <a class="btn btn-sm btn-default" href="#" data-action="unchecked">
                <i class="fa fa-square-o"></i> <?= $this->translate('show unchecked') ?>
            </a>
        </div>

    </div>
</div>
<div id="columnContainer" class="dataTables_wrapper"<?php echo devhelp(__FILE__) ?>>
    <table class="table table-head-custom dataTable table-responsive">
        <thead>
        <tr class="draggable-zone">
            <?php
            /*
             * =========================================================================
             * DRAW TABLE HEADER COLS
             * =========================================================================
             */
            foreach ($columns as $column):
                $colname = $column['name'];

                $checked = '';
                if (isset($currentColumns[$colname]) && $currentColumns[$colname]['hidden'] === false) {
                    $checked = ' checked';
                }
                ?>
                <th id="column_<?= $colname ?>" data-colname="<?php echo $colname; ?>" class="text-center draggable" scope="col">
                    <p class="text-nowrap">
                        <?php echo(!empty($column['label']) ? $this->translate($column['label']) : '') ?>
                        <a class="btn btn-sm btn-icon">
                            <i class="la la-exchange-alt draggable-handle"></i>
                        </a>
                    </p>
                    <p>
                        <label class="checkbox checkbox-outline checkbox-success justify-content-center">
                            <input type="hidden" name="<?= $colname; ?>" value="0">
                            <input type="checkbox" name="<?= $colname; ?>" class="form-control" value="1"<?= $checked ?>>
                            <span></span>
                        </label>
                    </p>
                </th>
            <?php endforeach; ?>
        </tr>
        </thead>
    </table>
</div>

<script>


    <?php

    //$this->headLink()->appendStylesheet('/assets/plugins/custom/draggable/draggable.bundle.css');

    $this->headScript()->appendFile('/assets/plugins/custom/draggable/draggable.bundle.js');
    $this->inlineScript()->captureStart();
    ?>
    $(document).ready(function () {
        // sortable
        var container = document.querySelector('.draggable-zone');
        new Sortable.default(container, {
            draggable: '.draggable',
            handle: '.draggable .draggable-handle'
        });
        // checkboxes
        var allCheckboxes = $('#columnContainer input[type=checkbox]');

        $('#columnActions a').on('click', function (e) {
            e.preventDefault();
            $a = $(this);
            var action = $a.data('action');
            switch (action) {
                case 'select':
                    allCheckboxes.prop('checked', true);
                    allCheckboxes.parents('th').show();
                    break;
                case 'deselect':
                    allCheckboxes.prop('checked', false);
                    allCheckboxes.parents('th').show();
                    break;
                case 'all':
                    allCheckboxes.parents('th').show();
                    break;
                case 'checked':
                    allCheckboxes.each(function (i, input) {
                        if ($(input).prop('checked')) {
                            $(input).parents('th').show();
                        } else {
                            $(input).parents('th').hide();
                        }
                    });
                    break;
                case 'unchecked':
                    allCheckboxes.each(function (i, input) {
                        if ($(input).prop('checked')) {
                            $(input).parents('th').hide();
                        } else {
                            $(input).parents('th').show();
                        }
                    });
                    break;
            }
        });
        /* ==== eo click ==== */
    });
    <?php
    $this->inlineScript()->captureEnd();
    ?>
</script>
