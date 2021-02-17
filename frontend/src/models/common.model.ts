import Dictionary from "../contracts/base.contract";
import date from 'date-and-time';

export class PhpDate {
    date: string
    timezone_time: number
    timezone: string

    constructor(date: string, timezone_time: number, timezone: string) {
        this.date = date
        this.timezone_time = timezone_time
        this.timezone = timezone
    }

    static fromDict(data: Dictionary<string|any>): PhpDate {
        return new PhpDate(
            data['date'],
            data['timezone_time'],
            data['timezone']
        )
    }

    getFormattedDate() {
        let tz = parseInt(date.format(new Date(this.date), "Z").replace("+", "")) / 100
        return date.format(date.addHours(new Date(this.date), tz), "Y-MM-DD HH:mm:SS")
    }

    substract(secondDate: PhpDate) {
        return date.subtract(new Date(secondDate.date), new Date(this.date))
    }
}
