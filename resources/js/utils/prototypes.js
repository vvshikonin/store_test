
Number.prototype.priceFormat = function (withCurrency) {
    return this.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + (withCurrency ? " ₽" : "");
}

String.prototype.priceFormat = function (withCurrency) {
    return parseFloat(this).priceFormat(withCurrency);
}

Array.prototype.equals = function (array) {
    if (!array)
        return false;
    if (array === this)
        return true;
    if (this.length != array.length)
        return false;

    for (var i = 0, l = this.length; i < l; i++) {
        if (this[i] instanceof Array && array[i] instanceof Array) {
            if (!this[i].equals(array[i]))
                return false;
        }
        else if (this[i] != array[i]) {
            return false;
        }
    }
    return true;
}

Object.defineProperty(Array.prototype, "equals", { enumerable: false });
