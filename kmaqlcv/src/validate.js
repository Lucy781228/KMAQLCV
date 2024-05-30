const validationMethods = {
    requiredString(value) {
      if (typeof value === 'string') {
        return value.trim() !== '';
    }
    return true;
    },
  
    requiredObject(value) {
      return value !== null;
    },
  
    email(value) {
      if(value === "") return true
      const emailRegex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
      return emailRegex.test(value);
    },
  
    phone(value) {
      if(value === "") return true
      const phoneRegex = /^\d{10}$/;
      return phoneRegex.test(value);
    },
  
    idNumber(value) {
      if(value === "") return true
      const idNumberRegex = /^(?:\d{9}|\d{12})$/;
      return idNumberRegex.test(value);
    },
  
    fullName(value) {
      if(value === "") return true
      const fullNameRegex = /^[a-zA-Z\s]+$/;
      return fullNameRegex.test(value);
    },
  
    number(value) {
      if(value === "") return true
      const numberRegex = /^\d+$/;
      return numberRegex.test(value);
    },

    decimal(value) {
      if(value === "") return true
      const decimalNumberRegex = /^\d+(\.\d+)?$/;
      return decimalNumberRegex.test(value);
    }
  };
  
  export default validationMethods;