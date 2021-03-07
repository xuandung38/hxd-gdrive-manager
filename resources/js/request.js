import axios from 'axios';
import _ from 'lodash';

const globalConfig = {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
};

const uploadConfig = {
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'multipart/form-data'
    },
};

export const Request = {
    get: async (uri, data = {}) => await axios.get(uri, data),
    upload: async (uri, data = {}) => await axios.post(uri, data, uploadConfig),
    post: async (uri, data = {}) => await axios.post(uri, data, globalConfig),
    put: async (uri, data = {}) => await axios.put(uri, data, globalConfig),
    patch: async (uri, data = {}) => await axios.patch(uri, data, globalConfig),
    delete: async (uri) => await axios.delete(uri, globalConfig),
    errors: (errorResponse) => {
        if (!errorResponse) {
            return null;
        }
        // If isset errors when form validate
        if (!_.isEmpty(_.get(errorResponse, 'data.errors'))) {
            const errors = errorResponse.data.errors;
            return errors[Object.keys(errors)[0]];
        }

        // Error for some case special
        if (!_.isEmpty(_.get(errorResponse, 'data.error'))) {
            const error = errorResponse.data.error;
            // eslint-disable-next-line no-console
            return errorResponse.status === 500
                ? 'Something wrong. Try again later!'
                : [error];
        }
        // Error when handle abort manual
        if (!_.isEmpty(_.get(errorResponse, 'data.message'))) {
            return [errorResponse.data.message];
        }

        return null;
    },
};
