export const computePermissions = (roles) => {
    const isAdmin = roles.includes('ROLE_ADMIN');
    const isUser = roles.includes('ROLE_USER');

    return {
        createTask: isAdmin,
        editTask: isAdmin,
        deleteTask: isAdmin,
        manageUsers: isAdmin
    };
};
