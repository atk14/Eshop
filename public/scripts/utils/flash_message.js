window.UTILS = window.UTILS || { };
/**
 * Class for displaying flash messages
 * 
 * Usage:
 * window.UTILS.FlashMessage.create( { message: "hello", style: "danger", dismissible: true } );
 * 
 * Options:
 * message: text to display
 * style: color style - alert | danger | success | info | warning
 * dismissible: if true close button would be added 
 * 
 */

window.UTILS.FlashMessage = class {
  /**
   * Displays flah message
   * @param {} options 
   */
  static create( options ) {
    const defaults = {
      message:      "",
      style:        "info",
      dismissible:  true,
    };
    options = {...defaults, ...options};

    if( document.querySelector( ".flash_messages" ) ) {
      this.cleanOldMessages();
      let elem = this.createAlertElement( options );
      document.querySelector( ".flash_messages" ).append( elem );
    } else {
      console.log( options.message );
    }


  }

  /**
   * Creates alert HTML node
   * @param {*} options 
   * @returns HTML Node
   */
  static createAlertElement( options ) {

    let elem = document.createElement( "div" );

    if( window.bootstrapVersion && window.bootstrapVersion === 5 ) {
      // BOOTSTRAP 5
      elem.classList.add( "alert", "alert-" + options.style, "fade", "show" );
      elem.setAttribute( "role", "alert" );
      if( options.dismissible ) {
        elem.classList.add( "alert-dismissible" );
        let btn = document.createElement( "button" );
        btn.classList.add( "btn-close" );
        btn.setAttribute( "data-bs-dismiss", "alert" );
        btn.setAttribute( "aria-label", "close" );
        elem.append( btn );
      }
    } else {
      // BOOTSTRAP 4
      elem.classList.add( "alert", "alert-" + options.style, "fade", "show" );
      if( options.dismissible ){
        let btn = document.createElement( "button" );
        btn.classList.add( "close" );
        btn.setAttribute( "type", "button" );
        btn.dataset.dismiss = "alert";
        btn.append( "×" );
        elem.append( btn );
      }
    }

    elem.append( options.message );

    return elem;
  }

  /**
   * Removes old flash messages that are already hidden
   */
  static cleanOldMessages() {
    let existingAlerts = document.querySelectorAll( ".flash_messages .alert" );
    [...existingAlerts].forEach( function( el ) {
      let style = window.getComputedStyle( el );
      if( style.getPropertyValue( "visibility" ) === "hidden" ) {
        el.remove();
      }
    } );
  }

};