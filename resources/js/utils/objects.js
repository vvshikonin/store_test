export const makeFormData = (data, putMethod = false,) => {
    const formData = new FormData();
    for (const key in data) {
        if (Array.isArray(data[key])) {
            data[key].forEach((item, index) => {
                if (typeof item === 'object') {
                    Object.keys(item).forEach(itemKey => {
                        formData.append(`${key}[${index}][${itemKey}]`, item[itemKey]);
                    });
                } else {
                    formData.append(`${key}[${index}]`, item);
                }
            });
        } else {
            formData.append(key, data[key]);
        }
    }

    if (putMethod) formData.append('_method', 'put');

    return formData;
}
