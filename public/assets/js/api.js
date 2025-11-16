const API = {
    baseURL: '/library-pro/api',

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            credentials: 'same-origin'
        };

        if (options.body) {
            config.body = JSON.stringify(options.body);
        }

        if (options.params) {
            const params = new URLSearchParams(options.params);
            return fetch(`${url}?${params}`, config).then(this.handleResponse);
        }

        return fetch(url, config).then(this.handleResponse);
    },

    handleResponse(response) {
        return response.json().then(data => {
            if (!response.ok) {
                throw data;
            }
            return data;
        });
    },

    auth: {
        login: (email, password) => API.request('/auth/login', {
            method: 'POST',
            body: { email, password }
        }),
        register: (userData) => API.request('/auth/register', {
            method: 'POST',
            body: userData
        }),
        logout: () => API.request('/auth/logout', { method: 'POST' }),
        getCurrentUser: () => API.request('/auth/current')
    },

    books: {
        getAll: (params) => API.request('/books', { params }),
        getById: (id) => API.request(`/books/${id}`),
        create: (data) => API.request('/books', { method: 'POST', body: data }),
        update: (id, data) => API.request(`/books/${id}`, { method: 'PUT', body: data }),
        delete: (id) => API.request(`/books/${id}`, { method: 'DELETE' }),
        search: (query, params) => API.request('/books/search', { params: { q: query, ...params } })
    },

    users: {
        getAll: (params) => API.request('/users', { params }),
        getById: (id) => API.request(`/users/${id}`),
        create: (data) => API.request('/users', { method: 'POST', body: data }),
        update: (id, data) => API.request(`/users/${id}`, { method: 'PUT', body: data }),
        delete: (id) => API.request(`/users/${id}`, { method: 'DELETE' })
    },

    loans: {
        getAll: (params) => API.request('/loans', { params }),
        getById: (id) => API.request(`/loans/${id}`),
        checkout: (data) => API.request('/loans/checkout', { method: 'POST', body: data }),
        return: (id) => API.request(`/loans/${id}/return`, { method: 'POST' }),
        getOverdue: () => API.request('/loans/overdue')
    },

    categories: {
        getAll: (params) => API.request('/categories', { params }),
        getById: (id) => API.request(`/categories/${id}`),
        create: (data) => API.request('/categories', { method: 'POST', body: data }),
        update: (id, data) => API.request(`/categories/${id}`, { method: 'PUT', body: data }),
        delete: (id) => API.request(`/categories/${id}`, { method: 'DELETE' })
    },

    authors: {
        getAll: () => API.request('/authors'),
        getById: (id) => API.request(`/authors/${id}`),
        create: (data) => API.request('/authors', { method: 'POST', body: data }),
        update: (id, data) => API.request(`/authors/${id}`, { method: 'PUT', body: data }),
        delete: (id) => API.request(`/authors/${id}`, { method: 'DELETE' })
    },

    reports: {
        getDashboard: () => API.request('/reports/dashboard'),
        getTrends: (startDate, endDate) => API.request('/reports/trends', {
            params: { start_date: startDate, end_date: endDate }
        }),
        getPopularBooks: (limit = 10) => API.request('/reports/popular-books', { params: { limit } }),
        getCategoryDistribution: () => API.request('/reports/categories'),
        getActiveUsers: (limit = 10) => API.request('/reports/active-users', { params: { limit } })
    }
};
