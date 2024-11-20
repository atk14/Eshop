window.UTILS = window.UTILS || { };

/**
 * Form validator
 */

window.UTILS.FormValidator = class {
  constructor(formElement) {
      this.form = formElement;
      this.errors = new Map();
      
      // Bind the submit handler
      this.form.addEventListener('submit', (e) => this.handleSubmit(e));
  }

  // Validate all form fields
  validateAll() {
      this.errors.clear();
      const inputs = this.form.querySelectorAll('input, select, textarea');
      
      inputs.forEach(input => {
          // Skip submit, reset, button, and hidden inputs
          if (['submit', 'reset', 'button', 'hidden'].includes(input.type)) {
              return;
          }
          
          this.validateField(input);
      });

      // Check if passwords match (if both fields exist)
      const password = this.form.querySelector('input[name="password"]');
      const passwordRepeat = this.form.querySelector('input[name="password_repeat"]');
      if (password && passwordRepeat) {
          this.validatePasswordMatch(password, passwordRepeat);
      }

      return this.errors.size === 0;
  }

  // Validate individual field
  validateField(input) {
      // Remove existing validation states
      input.classList.remove('is-valid', 'is-invalid');
      
      // Remove any existing error messages
      const parentEl = input.closest('.form-group') || input.parentElement;
      const existingFeedback = parentEl.querySelector('.invalid-feedback, .valid-feedback');
      if (existingFeedback) existingFeedback.remove();

      // Skip validation for empty non-required fields
      if (!input.hasAttribute('required') && !input.value.trim()) {
          return;
      }

      // Custom validation logic
      let isValid = true;
      let errorMessage = '';

      // Required field validation
      if (input.hasAttribute('required') && !input.value.trim()) {
          isValid = false;
          errorMessage = 'This field is required';
      }

      // Pattern validation
      if (isValid && input.hasAttribute('pattern') && input.value.trim()) {
          const pattern = new RegExp(input.getAttribute('pattern'));
          if (!pattern.test(input.value)) {
              isValid = false;
              errorMessage = 'Invalid format';
          }
      }

      // Email validation
      if (isValid && (input.type === 'email' || input.name.toLowerCase() === 'email') && input.value.trim()) {
          const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailPattern.test(input.value)) {
              isValid = false;
              errorMessage = 'Invalid email address';
          }
      }

      // Phone validation
      if (isValid && input.name.toLowerCase() === 'phone' && input.value.trim()) {
          const cleanPhone = input.value.replace(/\s+/g, '');
          const phonePattern = /^(\+)?420\d{9}$/;
          if (!phonePattern.test(cleanPhone)) {
              isValid = false;
              errorMessage = 'Invalid phone number format (+420123456789 or 420123456789)';
          }
      }

      // Apply validation state
      if (isValid) {
          input.classList.add('is-valid');
          this.createFeedbackElement(parentEl, 'valid-feedback', '');
      } else {
          input.classList.add('is-invalid');
          this.createFeedbackElement(parentEl, 'invalid-feedback', errorMessage);
          this.errors.set(input, errorMessage);
      }
  }

  // Create feedback element
  createFeedbackElement(parentEl, className, message) {
      const feedbackEl = document.createElement('div');
      feedbackEl.className = className;
      feedbackEl.textContent = message;
      parentEl.appendChild(feedbackEl);
  }

  // Validate password match
  validatePasswordMatch(password, passwordRepeat) {
      if (password.value !== passwordRepeat.value) {
          const errorMessage = 'Passwords do not match';
          passwordRepeat.classList.remove('is-valid');
          passwordRepeat.classList.add('is-invalid');
          
          const parentEl = passwordRepeat.closest('.form-group') || passwordRepeat.parentElement;
          this.createFeedbackElement(parentEl, 'invalid-feedback', errorMessage);
          
          this.errors.set(passwordRepeat, errorMessage);
      }
  }

  // Handle form submission
  handleSubmit(e) {
      // Add Bootstrap's needs-validation class
      this.form.classList.add('was-validated');

      if (!this.validateAll()) {
          e.preventDefault();
      }
  }

  // Get all current errors
  getErrors() {
      return this.errors;
  }
};


/*
// Floating cart info show/hide
window.UTILS.FormValidator = class {

  #result = false;

  constructor() {
    console.log( "hello form validator" );
    const docForms = document.querySelectorAll( "form" );
    [...docForms].forEach( this.setHandlers.bind( this ) );
  }
  
  setHandlers( form ) {
    form.style.border = "1px dotted red";
    form.addEventListener( "submit", this.onSubmit.bind( this ) );
  }

  onSubmit( e ){
    let form = e.target;
    this.#result = false;
    console.log( "SUBMISSIOn eVENT", form );
    let requiredFields = form.querySelectorAll( "[required]" );
    if( requiredFields ) {
      e.preventDefault();
      console.log( "RESULT", this.checkRequiredFields( requiredFields ) )
    }
    e.preventDefault();
  }

  checkRequiredFields( requiredFields ) {
    [...requiredFields].forEach( this.validateField.bind( this ) );
  }

  validateField( field ) {
    field.style.outline = "1mm solid lime";
    let inputType = null;
    let tagName = field.tagName;

    if( tagName.toLowerCase() === "input" ) {
      inputType = field.getAttribute( "type" );
      switch ( inputType ) {
        case "text":

        break;
        case "password":

        break;


      }
    }
    console.log(field, {tagName},{inputType});
  }

};
*/