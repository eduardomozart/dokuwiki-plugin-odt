<?php

require_once DOKU_PLUGIN.'odt/ODT/elements/ODTStateElement.php';

/**
 * ODTElementTable:
 * Class for handling the table element.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  LarsDW223
 */
class ODTElementTable extends ODTStateElement
{
    // Table specific state data
    protected $table_column_styles = array ();
    protected $table_style = NULL;
    protected $table_autocols = false;
    protected $table_maxcols = 0;
    protected $table_curr_column = 0;

    // Flag indicating that a table was created inside of a list
    protected $list_interrupted = false;

    /**
     * Constructor.
     * ($numrows is currently unused)
     */
    public function __construct($style_name=NULL, $maxcols = 0, $numrows = 0) {
        parent::__construct();
        $this->setClass ('table');
        if ($style_name != NULL) {
            $this->setStyleName ($style_name);
        }
        $this->setTableMaxColumns($maxcols);
        if ($maxcols == 0) {
            $this->setTableAutoColumns(true);
        }
    }

    /**
     * Return the elements name.
     * 
     * @return string The ODT XML element name.
     */
    public function getElementName () {
        return ('table:table');
    }

    /**
     * Return string with encoded opening tag.
     * 
     * @return string The ODT XML code to open this element.
     */
    public function getOpeningTag () {
        $style_name = $this->getStyleName();
        if ($style_name == NULL) {
            $encoded = '<table:table>';
        } else {
            $encoded .= '<table:table table:style-name="'.$style_name.'">';
        }
        $maxcols = $this->getTableMaxColumns();
        $count = $this->getCount();
        if ($maxcols == 0) {
            // Try to automatically detect the number of columns.
            $this->setTableAutoColumns(true);
        } else {
            $this->setTableAutoColumns(false);
        }
        
        // Add column definitions placeholder.
        // This will be replaced on tabelClose()/getClosingTag()
        $encoded .= '<ColumnsPlaceholder'.$count.'>';

        // We start with the first column
        $this->setTableCurrentColumn(0);

        return $encoded;
    }

    /**
     * Return string with encoded closing tag.
     * 
     * @return string The ODT XML code to close this element.
     */
    public function getClosingTag (&$content = NULL) {
        // Generate table column definitions and replace the placeholder with it
        $count = $this->getCount();
        $max = $this->getTableMaxColumns();
        if ($max > 0 && $content != NULL) {
            $column_defs = '';
            for ($index = 0 ; $index < $max ; $index++) {
                $styleName = $this->getTableColumnStyleName($index);
                if (!empty($styleName)) {
                    $column_defs .= '<table:table-column table:style-name="'.$styleName.'"/>';
                } else {
                    $column_defs .= '<table:table-column/>';
                }
            }
            $content =
                str_replace ('<ColumnsPlaceholder'.$count.'>', $column_defs, $content);
        }

        return '</table:table>';
    }

    /**
     * Are we in a paragraph or not?
     * As a table we are not.
     * 
     * @return boolean
     */
    public function getInParagraph() {
        return false;
    }

    /**
     * Determine and set the parent for this element.
     * As a table the previous element is our parent.
     *
     * @param ODTStateElement $previous
     */
    public function determineParent(ODTStateElement $previous) {
        $this->setParent($previous);
    }

    /**
     * Set table column styles 
     * 
     * @param array $value
     */
    public function setTableColumnStyles($value) {
        $this->table_column_styles = $value;
    }

    /**
     * Set table column style for $column
     * 
     * @param array $value
     */
    public function setTableColumnStyleName($column, $style_name) {
        $this->table_column_styles [$column] = $style_name;
    }

    /**
     * Get table column styles
     * 
     * @return array
     */
    public function getTableColumnStyles() {
        return $this->table_column_styles;
    }

    /**
     * Set table column style for $column
     * 
     * @param array $value
     */
    public function getTableColumnStyleName($column) {
        return $this->table_column_styles [$column];
    }

    /**
     * Set flag if table columns shall be generated automatically.
     * (automatically detect the number of columns)
     * 
     * @param boolean $value
     */
    public function setTableAutoColumns($value) {
        $this->table_autocols = $value;
    }

    /**
     * Get flag if table columns shall be generated automatically.
     * (automatically detect the number of columns)
     * 
     * @return boolean
     */
    public function getTableAutoColumns() {
        return $this->table_autocols;
    }

    /**
     * Set maximal number of columns.
     * 
     * @param integer $value
     */
    public function setTableMaxColumns($value) {
        $this->table_maxcols = $value;
    }

    /**
     * Get maximal number of columns.
     * 
     * @return integer
     */
    public function getTableMaxColumns() {
        return $this->table_maxcols;
    }

    /**
     * Set current column.
     * 
     * @param integer $value
     */
    public function setTableCurrentColumn($value) {
        $this->table_curr_column = $value;
    }

    /**
     * Get current column.
     * 
     * @return integer
     */
    public function getTableCurrentColumn() {
        return $this->table_curr_column;
    }

    /**
     * Get the predefined style name for the current
     * table column.
     * 
     * @return string
     */
    public function getCurrentTableColumnStyleName() {
        $table_column_styles = $this->getTableColumnStyles();
        $curr_column = $this->getTableCurrentColumn();
        return $table_column_styles [$curr_column];
    }

    /**
     * Set flag if current list is interrupted (by a table) or not.
     * 
     * @param boolean $value
     */
    public function setListInterrupted($value) {
        $this->list_interrupted = $value;
    }

    /**
     * Get flag if current list is interrupted (by a table) or not.
     * 
     * @return boolean
     */
    public function getListInterrupted() {
        return $this->list_interrupted;
    }
}
