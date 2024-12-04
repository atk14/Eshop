window.UTILS = window.UTILS || { };
/**
 * Form validator
 * Automatically validates forms with attributes data-validation-rules and data-validation-messages
 * 
 * Requires: jQuery, jquery-validator
 * 
 * Usage:
 * new window.UTILS.FormValidator();
 * 
 * Useful method:
 * addForm( form ); - adds form to validator if it was not added automatically 
 * 
 */

window.UTILS.FormValidator = class {
  $ = window.jQuery;

  constructor() {
    console.log( "FormValidator", $.validator );
    if( !$.validator.methods.regex ){
      this.#configureJQValidator();
    }
    console.log( $.validator.methods.regex );

    const forms = document.querySelectorAll( "form[data-validation-rules][data-validation-messages]" );
    [...forms].forEach( this.addForm.bind( this ) );

  }

  #configureJQValidator() {
    $.validator.addMethod( "regex", function( value, element, regexp ) {

      // Regexp comes here as '/^[a-z]{1,5}$/i'
      // It must be splitted to: pattern='^[a-z]{1,5}', modifiers='i'
      var matches = regexp.match( /\/(.*)\/([^\/]*)/ ),
        pattern = matches[ 1 ],
        modifiers = matches[ 2 ],
        re = new RegExp( pattern, modifiers );

      return this.optional( element ) || re.test( value );
    }, "Please check your input." );
  }

  addForm( frm ) {
    // For debug to notice that form is js validated
    let flag = document.createElement("div");
    flag.textContent = "this form is JS validated";
    flag.classList.add( "bg-danger", "text-white", "py-1", "px-2", "d-inline-block" );
    flag.style.fontSize = "0.7rem"
    frm.prepend( flag );


    // Validate form on keyup and submit
    let $form = $( frm ),
    rules = $form.data( "validation-rules" ),
    messages = $form.data( "validation-messages" );

    console.log( rules );
    console.log( messages );

    $form.validate( {
      rules: rules,
      messages: messages,

      // The errorPlacement has to take the table layout into account
      errorPlacement: function( error, element ) {

        // hack to remove existing error messages to avoid displaying multiple old errors under login field
        element.closest( ".form-group" ).find( "div.error" ).remove();

        // place error message into parent .form-group
        error.appendTo( element.closest( ".form-group" ) );
      },
      errorElement: "div",

      // On invalid form submit
      invalidHandler: function() {
        if( window.UTILS.FlashMessage ) {
          window.UTILS.FlashMessage.create( { message: "TODO: lokalizovaný text o tom, že se to má správně vyplnit", style: "danger"} );
        }
      },

    } );
  }

};