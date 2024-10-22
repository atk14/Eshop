/**
 * Class for communication between multiple browser instances (windows, tabs) of this site
 * Used for syncing basket status, favourites status etc. between open tabs
 * 
 * Usage:
 * new window.UTILS.WindowSync();
 *  
 * Listens to events: basket_updated, favourites_updated (when these changed in this window)
 * Emits events: basket_remote_updated, favourites_remote_updated (when these changed in other window)
 * 
 * Useful method:
 * send( data ): sends any data to all other window instances 
 *
 * 
 */
window.UTILS = window.UTILS || { };

window.UTILS.WindowSync = class {

  sync;           // shared worker instance
  lastMsgId = 0;  // id of last message sent

  constructor() {
    this.sync = new BroadcastChannel( "atk14_radio" );
    this.sync.onmessage = this.onSyncMessage.bind( this );
    /*this.sync.addEventListener( "messageerror", function( e ) {
      throw new Error( "BroadcastChannel Error: could not open SharedWorker", e );
    }, false);*/
    this.sync.onmessageerror = function( e ) {
      throw new Error( "BroadcastChannel Error: could not open SharedWorker", e );
    };
    this.testHandlers();
    this.setWindowEventHandlers();
    console.log( "----------BroadcastChannel", this.sync );
  }

  // Incoming message
  onSyncMessage( e ) {
    // if message comes from our instance ignore it / return
    if( e.data.msgID === this.lastMsgId ) {
      console.log( "this is our own message" );
      return;
    }

    // write message to test div
    document.getElementById( "synctest-output" ).append( e.data.data + "\n" );

    // Process known messages, ignore unknown messages
    if( e.data.data ){
      let eventName = null;
      // Check for valid messages
      switch ( e.data.data ){
        case "basket_updated":
          eventName = "basket_remote_updated";
          break;
        case "favourites_updated":
          eventName = "favourites_remote_updated";
          break;
        // add more actions if needed
      }
      // Trigger appropriate event if message was valid
      if( eventName ) {
        console.log( "WindowSync event:", eventName );
        window.dispatchEvent( new Event( eventName ) );
      } else {
        console.warn( "Invalid WindowSync event:", e.data.data );
      }
    }
    
  }

  // Method to serd message to all other browser instances
  send( data ) {
    // Make unique ID
    let msgID = Math.floor(Math.random() * 100).toString() + Date.now();
    // Send
    this.sync.postMessage( { data: data , msgID: msgID } );
    // Remember sent message ID
    this.lastMsgId = msgID;
  }

  // Testing
  testHandlers() {
    let form = document.getElementById( "synctest" );
    form.addEventListener( "submit", function( e ) {
      e.preventDefault();
      this.send( document.getElementById( "synctest-input" ).value );
      document.getElementById( "synctest-output" ).append( "    " + document.getElementById( "synctest-input" ).value + "\n" );
    }.bind( this ) );
  }

  // Listen for local events (local basket add/remove, local favourites add/remove etc.)
  setWindowEventHandlers() {
    window.addEventListener( "basket_updated", this.onBasketUpdate.bind( this ) );
    window.addEventListener( "favourites_updated", this.onFavoritesUpdate.bind( this ) );
    window.addEventListener( "beforeunload", this.close.bind( this ) );
  }

  // On local basket update
  onBasketUpdate() {
    console.log( "BASKET UPDATED EVENT" );
    console.log( "--------------------" );
    this.send( "basket_updated" )
  }

  // On local favorites update
  onFavoritesUpdate() {
    console.log( "FAVOURITES UPDATED EVENT" );
    console.log( "--------------------" );
    this.send( "favourites_updated" )
  }

  // Closes connection. Called on page unload.
  close(){
    console.log( "CLOSING CONNECTION" );
    this.sync.close();
  }

};