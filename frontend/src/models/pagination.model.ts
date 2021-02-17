import Dictionary from "../contracts/base.contract";


export default class Pagination {
    current: number
    max: number
    perPage: number

    constructor(current: number, max: number, perPage: number) {
        this.current = current
        this.max = max
        this.perPage = perPage
    }

    /**
     * Factory method
     *
     * @param pagination
     */
    static fromDict(pagination: Dictionary<any>): Pagination {
        if (!pagination) {
            return new Pagination(1, 1, 0)
        }

        return new Pagination(
            pagination['page'],
            pagination['maxPages'],
            pagination['perPageLimit']
        )
    }
}
