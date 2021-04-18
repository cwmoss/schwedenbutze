/*

npm install date-fns --save

this needs version 2
*/

import { format, add, parse, isSameYear, isSameMonth, formatDistanceStrict, eachDayOfInterval } from 'date-fns'
import { de } from 'date-fns/locale'

export function str_to_date(str){
	// 2021-03-25
	// 2021/03/25 would be also possible
	str = str.replaceAll('/', '-')
	return parse(str, 'yyyy-MM-dd', new Date())
}

export function format_mysql(date){
	return format(date, 'yyyy-MM-dd')
}

export function format_interval(start, end){
	start = str_to_date(start)
	end = str_to_date(end)
	var fdates

	if(!isSameYear(start, end)){
		fdates = [format(start, 'd.MM.yyyy'), format(end, 'd.MM.yyyy')]
	}else if(!isSameMonth(start, end)){
		fdates = [format(start, 'd.MM.'), format(end, 'd.MM.yyyy')]
	}else{
		fdates = [format(start, 'd.'), format(end, 'd.MM.yyyy')]
	}
	// use the beautifuly em-dash here!
	return fdates.join('—')
}

export function days(start, end){
	return formatDistanceStrict(str_to_date(start), str_to_date(end), {
		locale: de
	})
}

export function days_strings(start, end){
	// console.log("days-string", start, end)
	return eachDayOfInterval({start: str_to_date(start), end: str_to_date(end)}).map(function(e){
		return format_mysql(e)
	})
}