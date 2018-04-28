(function ($, Drupal) {
  Drupal.behaviors.dynamicLayouts = {
    // Setup variables.
    structure: {},

    attach: function (context) {
      var _this = this;

      if (_this.isEmptyObject(_this.structure) === true) {
        _this.structure = _this.getStructure(context);
      }
      else {
        _this.compareStructure(context);
      }
    },

    getStructure: function (context) {
      var dataStructure = {};

      var rows = $('.dynamic-layout-row', context);
      $.each(rows, function () {
        var row = $(this);
        var columnIds = {};
        var rowId = $(this).data('row');

        var columns = row.find('.dynamic-layout-column');
        $.each(columns, function () {
          var columnId = $(this).data('column');
          columnIds[columnId] = columnId;
        });

        dataStructure[rowId] = columnIds;
      });

      return dataStructure;
    },

    compareStructure: function (context) {
      var _this = this;
      var newStructure = _this.getStructure(context);

      if (_this.isEmptyObject(newStructure) === false) {
        var addedElement = false;

        $.each(newStructure, function (row) {
          if (_this.structure[row] === undefined) {
            // This is a new row, so add a highlight class.
            addedElement = $('[data-row="' + row + '"]');
          }
          else {
            // A new columns has been added, so loop through the columns.
            $.each(newStructure[row], function(column) {
              if (_this.structure[row][column] === undefined) {
                // This is a new column, so add a highlight class.
                addedElement = $('[data-column="' + column + '"]');
              }
            });
          }
        });

        if (addedElement !== false) {
          addedElement.addClass('added-element');
          $('html, body').animate({
            scrollTop: addedElement.offset().top - 100
          }, 250);

          _this.structure = newStructure;
        }
      }
    },

    isEmptyObject: function(obj) {
      for (var x in obj) { return false; }
      return true;
    }
  };
})(jQuery, Drupal);
