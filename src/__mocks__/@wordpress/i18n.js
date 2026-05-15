export const __ = ( str ) => str;
export const _n = ( single ) => single;
export const _x = ( str ) => str;
export const sprintf = ( fmt, ...args ) =>
	args.reduce( ( s, a ) => s.replace( /%s/, a ), fmt );
