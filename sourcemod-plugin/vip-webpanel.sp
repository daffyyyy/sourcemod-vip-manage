#include <sourcemod>
#include <sdkhooks>
#include <sdktools>
#include <cstrike>
#include <unixtime_sourcemod>

#define MAX_PLAYERS 32 

Handle sql;                                                                    
char dbError[512];  

ConVar CvarHostIp, CvarPort;
char ServerIp[32], ServerPort[7];
    
int iTime1, iYear1, iMonth1, iDay1, iHour1, iMinute1, iSecond1;
public Plugin:myinfo = 
{
    name = "VIP-WebPanel",
    author = "daffyy",
    description = "Give vip via webpanel",
    version = "1.0a",
    url = "https://daffyy.tech"
}

public OnPluginStart()
{
    GetServerInfo();
    DatabaseConnect();
}

public OnMapLoad()
{
    iTime1 = GetTime( );
    UnixToTime( iTime1 , iYear1 , iMonth1 , iDay1 , iHour1 , iMinute1 , iSecond1 );
}

public void OnClientPutInServer(int client)
{
    if(!IsValidClient(client) || IsFakeClient(client) || IsClientSourceTV(client))
        return;

    GetDateBySteamid(client);
    GiveFlagBySteamid(client);
}	

public Action:GiveFlagBySteamid(int client)
{
    new String:tmp[1024];
    decl String:player_authid[32];
    
    if ( GetClientAuthId(client, AuthId_Steam2, player_authid, sizeof(player_authid)))
    {
        Format(tmp, sizeof(tmp), "SELECT `flags` FROM `vips` WHERE (steamid = '%s' AND server_address = 'all') OR (steamid = '%s' AND server_address = '%s:%s') LIMIT 1;", player_authid, player_authid, ServerIp, ServerPort);
        SQL_TQuery(sql, GiveFlagBySteamidContinue, tmp, client);
    }
}                

public GiveFlagBySteamidContinue(Handle:owner, Handle:query, const String:error[], any:client)
{
    decl String:player_authid[32];
    
    if(query == INVALID_HANDLE)
    {
        LogError("Load error: %s", error);
        return;
    }
    
    if(SQL_GetRowCount(query))
    {
        new String:flags[64];
        
        while(SQL_FetchRow(query))
        {                
            SQL_FetchString(query, 0, flags, sizeof(flags));
                
            if ( GetClientAuthId(client, AuthId_Steam2, player_authid, sizeof(player_authid)))
            {
                if (StrEqual(flags, "VIP")) SetUserFlagBits(client, GetUserFlagBits(client)|ADMFLAG_RESERVATION|ADMFLAG_CUSTOM1);
                if (StrEqual(flags, "SVIP")) SetUserFlagBits(client, GetUserFlagBits(client)|ADMFLAG_RESERVATION|ADMFLAG_CUSTOM5);
            }    
        }
    }    
}

public Action:GetDateBySteamid(int client)
{
    new String:tmp[1024];
    decl String:player_authid[32];
    
    if ( GetClientAuthId(client, AuthId_Steam2, player_authid, sizeof(player_authid)))
    {
        Format(tmp, sizeof(tmp), "SELECT UNIX_TIMESTAMP(`expire`) FROM `vips` WHERE (steamid = '%s' AND server_address = 'all') OR (steamid = '%s' AND server_address = '%s:%s') LIMIT 1;", player_authid, player_authid, ServerIp, ServerPort);
        SQL_TQuery(sql, GetDateBySteamidContinue, tmp, client);
    }
}    

public GetDateBySteamidContinue(Handle:owner, Handle:query, const String:error[], any:client)
{
    decl String:player_authid[32];
    new String:tmp[1024];
    
    if(query == INVALID_HANDLE)
    {
        LogError("Load error: %s", error);
        return;
    }
    if(SQL_GetRowCount(query))
    {
        new String:date[512];
        new String:expirationdate[512];
        int dateint,expirationdateint;
        
        while(SQL_FetchRow(query))
        {
            Format(date, sizeof(date), "%02d-%02d-%d", iDay1 , iMonth1 , iYear1);
            SQL_FetchString(query, 0, expirationdate, sizeof(expirationdate));
                
            dateint = StringToInt(date);
            expirationdateint = DateToTimestamp(expirationdate);
            dateint = ((expirationdateint - dateint)/60/60/24);
                
            if (dateint >= 1)
            {
                PrintToChat(client, "\x01[\x04VIP\x01] Twój VIP wygasa za \x04%i dni\x01", dateint);
            } else if (dateint <= 0)
            {
                PrintToChat(client, "\x01[\x04VIP\x01] Twój VIP właśnie się skończył!");
                GetClientAuthId(client, AuthId_Steam2, player_authid, sizeof(player_authid));

                Format(tmp, sizeof(tmp), "DELETE FROM `vips` WHERE steamid = '%s' AND expire < '%s';", player_authid, date);
                SQL_TQuery(sql, WriteToDatabase_Handler, tmp, client);
            }
        }
    }
}

public Action:DatabaseConnect()
{
    sql = SQL_Connect("vip-webpanel", true, dbError, sizeof(dbError));
    if(sql == INVALID_HANDLE)
        PrintToServer("Could not connect: %s", dbError);
}

public WriteToDatabase_Handler(Handle:owner, Handle:query, const String:error[], any:client)
{
    if(query == INVALID_HANDLE)
    {
        LogError("Save error: %s", error);
        return;
    }
}

stock bool:IsValidClient(client)
{
    if(client >= 1 && client <= MaxClients && IsClientInGame(client))
        return true;
    
    return false;
}

// Just for date - Format: mm/dd/yyyy
stock int DateToTimestamp( const char[ ] szDate ) {
    char szBuffer[ 64 ];
    strcopy( szBuffer, sizeof( szBuffer ), szDate );
    
    ReplaceString( szBuffer, sizeof( szBuffer ), "-", " " );
    
    char szTime[ 3 ][ 6 ];
    ExplodeString( szBuffer, " ", szTime, sizeof( szTime ), sizeof( szTime[ ] ) );
    
    int iYear = StringToInt( szTime[ 2 ] );
    int iMonth  = StringToInt( szTime[ 0 ] );
    int iDay = StringToInt( szTime[ 1 ] );
    int iHour = 23;
    int iMinute  = 59;
    int iSecond = 59;
    
    return TimeToUnix( iYear, iMonth, iDay, iHour, iMinute, iSecond, UT_TIMEZONE_SERVER );
} 

stock void GetServerInfo()
{
    CvarHostIp = FindConVar("hostip");
    CvarPort = FindConVar("hostport");
    
    int pieces[4];
    int longip = GetConVarInt(CvarHostIp);

    pieces[0] = (longip >> 24) & 0x000000FF;
    pieces[1] = (longip >> 16) & 0x000000FF;
    pieces[2] = (longip >> 8) & 0x000000FF;
    pieces[3] = longip & 0x000000FF;

    FormatEx(ServerIp, sizeof(ServerIp), "%d.%d.%d.%d", pieces[0], pieces[1], pieces[2], pieces[3]);
    GetConVarString(CvarPort, ServerPort, sizeof(ServerPort));
}