export const isValidEmail = (email) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
};

export const isValidNumber = (text) => {
    const regex = /^[0-9]+$/;
    return regex.test(text);
};
